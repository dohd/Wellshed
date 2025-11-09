<?php

namespace App\Http\Controllers\Focus\payment_receipt;

use App\Http\Controllers\Controller;
use App\Models\payment_receipt\PaymentReceipt;
use App\Models\customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentReceiptsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('focus.payment_receipts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::whereHas('subscriptions')->with([
           'subscriptions' => fn($q) => $q->select('id', 'customer_id', 'sub_package_id')->where('status', 'active'),
           'subscriptions.package' => fn($q) => $q->select('id', 'name', 'price'),
           'orders' => fn($q) => $q->select('id', 'customer_id', 'tid', 'total'),
           'charges' => fn($q) => $q->select('id', 'customer_id', 'tid', 'notes', 'amount'),
        ])
        ->get(['id', 'company', 'name', 'phone'])
        ->map(function($c) {
            $c->orders = $c->orders->map(function($ord) {
                $ord->tid = gen4tid('ORD-', $ord->tid);
                return $ord;
            });
            $c->charges = $c->charges->map(function($rcpt) {
                $rcpt->tid = gen4tid('RCPT-', $rcpt->tid);
                return $rcpt;
            });
            return $c;
        });
        
        return view('focus.payment_receipts.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validate request
        $validator = Validator::make($request->all(), [
            'entry_type' => 'required',
            'customer_id' => 'required',
            'amount' => 'required',
            'date' => 'required',
            'payment_for' => 'required_if:entry_type,receipt',
            'payment_method' => 'required_if:entry_type,receipt',
        ], [
            'customer_id.required' => 'Customer is required',
            'subscription_id.required' => 'Subscription plan is required',
            'order_id.required' => 'Order Ref is required',
            'charge_id.required' => 'Charge Ref is required',
        ]);
        foreach (['subscription_id', 'order_id', 'charge_id'] as $field) {
            $validator->sometimes($field, 'required', function ($input) use ($field) {
                return $input->entry_type === 'receipt' && $input->payment_for === explode('_', $field)[0];
            });
        }

        if ($validator->fails()) {
            $errors = $validator->errors(); // This is a MessageBag
            // Get all errors as array
            $errorMessages = $errors->all();
            // Get specific field errors
            // $customerErrors = $errors->get('customer_id');
            return response()->json([
                'status' => 'error', 
                'message' => 'Validation failed! ' . implode(', ', $errorMessages),
                'errors' => $errors
            ], 422);
        }

        $input = $request->all();
        $main = $request->only([
            'entry_type', 'customer_id', 'amount', 'date', 'payment_method', 'payment_for', 'confirmed_at',
            'merchant_request_id', 'checkout_request_id',
        ]);

        try {
            $main['amount'] = numberClean($main['amount']);
            $main['date'] = date_for_database($main['date']);
            if (isset($input['payment_for'])) {
                if ($input['payment_for'] === 'subscription') {
                    $main = array_replace($main, [
                        'credit' => $main['amount'],
                        'subscription_id' => @$input['subscription']['subscription_id'],
                        'notes' => $input['subscription']['notes'] ?? $input['notes'],
                        'mpesa_ref' => @$input['refs']['mpesa']? strtoupper($input['refs']['mpesa']) : null,
                        'mpesa_phone' => $input['refs']['mpesa_phone'] ?? $input['mpesa_phone'],
                    ]);
                } 
                elseif ($input['payment_for'] === 'order') {
                    $main = array_replace($main, [
                        'credit' => $main['amount'],
                        'order_id' => @$input['order']['order_id'],
                        'notes' => $input['order']['notes'] ?? $input['notes'],
                        'mpesa_ref' => @$input['refs']['mpesa']? strtoupper($input['refs']['mpesa']) : null,
                        'mpesa_phone' => $input['refs']['mpesa_phone'] ?? $input['mpesa_phone'],
                    ]);
                } 
                elseif ($input['payment_for'] === 'charge') {
                    $main = array_replace($main, [
                        'credit' => $main['amount'],
                        'charge_id' => @$input['charge']['charge_id'],
                        'notes' => $input['charge']['notes'] ?? $input['notes'],
                        'mpesa_ref' => @$input['refs']['mpesa']? strtoupper($input['refs']['mpesa']) : null,
                        'mpesa_phone' => $input['refs']['mpesa_phone'] ?? $input['mpesa_phone'],
                    ]);
                }
            } else {
                $main = array_replace($main, [
                    'debit' => $main['amount'],
                    'notes' => $input['debit']['notes'],
                ]);
            }
            
            // validate MPESA Reference
            if (isset($main['mpesa_ref'])) {
                $mpesaRefExists = PaymentReceipt::whereNotNull('mpesa_ref') 
                ->where('mpesa_ref', $main['mpesa_ref'])->exists();
                if ($mpesaRefExists) {
                    return response()->json(['message' => "Mpesa reference {$main['mpesa_ref']} already exists"], 409);
                }
            }

            // validate debit reason
            if (isset($main['debit']) && empty($main['notes'])) {
                return response()->json(['message' => "Charge description required"], 422);
            }

            $receipt = PaymentReceipt::create($main);

            return response()->json(['message' => 'Receipt created successfully']);
        } catch (\Exception $e) {
            \Log::error($e->getMessage() . ' {user_id: ' . auth()->id() . '}' . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['message' => 'Error creating Receipt'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentReceipt $paymentreceipt)
    {
        try {
            $paymentreceipt->update(['deleted_at' => now(), 'deleted_by' => auth()->id()]);
        } catch (\Exception $e) {
            return errorHandler('Error deleting Receipt');
        }
    }
}
