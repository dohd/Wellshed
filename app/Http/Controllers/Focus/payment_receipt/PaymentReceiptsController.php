<?php

namespace App\Http\Controllers\Focus\payment_receipt;

use App\Http\Controllers\Controller;
use App\Models\payment_receipt\PaymentReceipt;
use App\Models\customer\Customer;
use Illuminate\Http\Request;

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
           'charges' => fn($q) => $q->select('id', 'customer_id', 'tid', 'notes'),
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
        $input = $request->all();
        $main = $request->only(['entry_type', 'customer_id', 'amount', 'date', 'payment_method', 'payment_for']);

        try {
            $main['amount'] = numberClean($main['amount']);
            $main['date'] = date_for_database($main['date']);
            if (isset($input['payment_for'])) {
                if ($input['payment_for'] === 'subscription') {
                    $main = array_replace($main, [
                        'credit' => $main['amount'],
                        'subscription_id' => $input['subscription']['subscription_id'],
                        'notes' => $input['subscription']['notes'],
                        'mpesa_ref' => strtoupper($input['refs']['mpesa']),
                        'mpesa_phone' => $input['refs']['mpesa_phone'],
                    ]);
                } 
                elseif ($input['payment_for'] === 'order') {
                    $main = array_replace($main, [
                        'credit' => $main['amount'],
                        'order_id' => $input['order']['order_id'],
                        'notes' => $input['order']['notes'],
                        'mpesa_ref' => strtoupper($input['refs']['mpesa']),
                        'mpesa_phone' => $input['refs']['mpesa_phone'],
                    ]);
                } 
                elseif ($input['payment_for'] === 'charge') {
                    $main = array_replace($main, [
                        'credit' => $main['amount'],
                        'charge_id' => $input['charge']['charge_id'],
                        'notes' => $input['charge']['notes'],
                        'mpesa_ref' => strtoupper($input['refs']['mpesa']),
                        'mpesa_phone' => $input['refs']['mpesa_phone'],
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
                $mpesaRefExists = PaymentReceipt::where('mpesa_ref', $main['mpesa_ref'])->exists();
                if ($mpesaRefExists) {
                    return response()->json(['error' => "Mpesa reference {$main['mpesa_ref']} already exists"], 500);
                }
            }

            // validate debit reason
            if (isset($main['debit']) && empty($main['notes'])) {
                return response()->json(['error' => "Charge description required"], 402);
            }

            // dd($main, $input);
            $receipt = PaymentReceipt::create($main);

            return response()->json(['success' => 'Receipt created successfully']);
        } catch (\Exception $e) {
            \Log::error($e->getMessage() . ' {user_id: ' . auth()->id() . '}' . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Error creating Receipt'], 500);
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
