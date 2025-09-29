<?php

namespace App\Http\Controllers\Focus\marquee;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\customer\CustomersController;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\User\User;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\items\JournalItem;
use App\Models\marquee\OldSuperAdminMarquee;
use App\Models\marquee\OldUserMarquee;
use App\Models\marquee\SuperAdminMarquee;
use App\Models\marquee\UserMarquee;
use App\Repositories\Focus\customer\CustomerRepository;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MarqueeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \DateMalformedStringException
     */
    public function index(Request $request)
    {

        if (!access()->allow('set-marquee') || Auth::user()->ins !== 2) return response("", 403);

//        return $superAdminMarquee = SuperAdminMarquee::all();

        try {

            if ($request->ajax()) {

                $superAdminMarquee = SuperAdminMarquee::when(request('businessFilter'), function ($q) {
                    $q->where('business', intval(request('businessFilter')));
                })
                    ->get();

                return Datatables::of($superAdminMarquee)
                    ->editColumn('business', function ($model) {

                        $bizId = $model->business;
                        $business = '<b style="color: #0DFF55"><i> All PME Users </i></b>';

                        if ($bizId) $business = optional(Company::withoutGlobalScopes()->find($bizId))->cname;

                        return $business;
                    })
                    ->editColumn('start', function ($model) {

                        return (new DateTime($model->start))->format('jS F Y | H:i') . ' hrs';
                    })
                    ->editColumn('end', function ($model) {

                        return (new DateTime($model->end))->format('jS F Y | H:i') . ' hrs';
                    })
                    ->addColumn('action', function ($model) {

                        $routeDelete = route('biller.marquee-delete', $model->id);

                        return '<a href="' . $routeDelete . '" 
                            class="btn btn-danger round" data-method="delete"
                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Delete"
                            >
                                <i  class="fa fa-trash"></i>
                            </a>';

                    })
                    ->rawColumns(['action', 'business'])
                    ->make(true);

            }

            $businesses = Company::where('id', '!=', Auth::user()->ins)->select('cname', 'id')->get();

        } catch (\Exception $e) {

            return $errorMessage = json_encode([
                'type' => "Subscription Payment Error!!!",
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
        return view('focus.marquee.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!access()->allow('set-marquee')) return response("", 403);

        $adminIns = User::find(130) ? User::find(130)->ins : false;

        $isAdmin = Auth::user()->ins === $adminIns;

        if(Auth::user()->ins === 2)
            $businesses = Company::where('id', '!=', Auth::user()->ins)
                ->where('status', 'Active')
                ->select('cname', 'id', 'billing_date')
                ->get()
                ->map(function ($company) {

                    // Retrieve the admin user's customer_id
                    $adminUser = User::where('ins', $company->id)->whereNotNull('tenant_customer_id')->first();
                    if (!$adminUser || !$adminUser->tenant_customer_id) {
                        return null;
                    }

                    // Find the customer without global scopes
                    $customer = Customer::withoutGlobalScopes()->find($adminUser->tenant_customer_id);
                    if (!$customer) {
                        return null;
                    }

                    // Calculate adjustment total
                    $adjustment_total = JournalItem::where('customer_id', $customer->id)
                        ->whereHas('account', fn($q) =>
                        $q->whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
                        )
                        ->whereHas('journal', fn($q) => $q->whereNull('paid_invoice_id'))
                        ->sum(DB::raw('debit-credit'));

                    // Get customer invoices and calculate the aging cluster
                    $customerRepo = new CustomerRepository();
                    $customerController = new CustomersController($customerRepo);
                    $invoices = $customerController->statement_invoices($customer);
                    $aging_cluster = $customerController->aging_cluster($customer, $invoices);

                    // Calculate the account balance
                    $account_balance = collect($aging_cluster)->sum() + $adjustment_total - $customer->on_account;

                    // Format billing date
                    $billing_date = $company->billing_date
                        ? (new DateTime($company->billing_date))->format('jS F, Y | H:i')
                        : 'Not Set';

                    return (object) [
                        'id' => $company->id,
                        'cname' => $company->cname .
                            " | Balance: " . number_format($account_balance, 2) .
                            " | Status: " . ($account_balance > 0 ? 'Overdue' : 'On Track') .
                            " | Billing Date: " . $billing_date,
            //            'balance' => $account_balance,
            //            'invoices' => $invoices,
            //            'aging_cluster' => $aging_cluster,
            //            'account_balance' => $account_balance,
            //            'customer' => $customer,
                    ];
                })
                ->filter(); // Remove null values

        else
            $businesses = Company::where('id', '!=', Auth::user()->ins)->select('cname', 'id')->get();

        return view('focus.marquee.create', compact('businesses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \DateMalformedStringException
     */
    public function store(Request $request)
    {

        if (!access()->allow('set-marquee')) return response("", 403);
        $isAdmin = Auth::user()->ins === 2;

         try {

             if ($isAdmin) {

//                 return $request;

                 try {
                     $validated = $request->validate([
                         'content' => ['required', 'string', 'max:2000'],
                         'business' => [
                             'required',
                             'array',
                             function ($attribute, $value, $fail) {
                                 foreach ($value as $companyId) {
                                     if (!Company::where('id', $companyId)->exists()) {
                                         $fail("The $attribute must contain valid company IDs.");
                                     }
                                 }
                             },
                         ],
                         'start' => ['required', 'date_format:Y-m-d\TH:i', 'after_or_equal:today'],
                         'end' => ['required', 'date_format:Y-m-d\TH:i', 'after:start'],
                     ]);
                 } catch (\Illuminate\Validation\ValidationException $e) {
                     return response()->json($e->errors());
                 }



                 for ($u = 0; $u < count($validated['business']); $u++) {

                     $previousMarquee = SuperAdminMarquee::where('business', $validated['business'][$u])->first();

                     if ($previousMarquee) {

                         $oldSuperAdminMarquee = new OldSuperAdminMarquee();
                         $oldSuperAdminMarquee->fill($previousMarquee->toArray());
                         $oldSuperAdminMarquee->save();
                     }

                     $marquee = SuperAdminMarquee::where('business', $validated['business'][$u])->first() ?? new SuperAdminMarquee();
                     $marquee->fill(Arr::except($validated, 'business'));
                     $marquee->business = $validated['business'][$u];
                     $marquee->save();
                 }
             } else {

                 $validated = $request->validate([
                     'content' => ['required', 'string', 'max:2000'],
                     'start' => ['required', 'date', 'after_or_equal:today'],
                     'end' => ['required', 'date', 'after:start'],
                 ]);

                 $previousMarquee = UserMarquee::first();

                 if ($previousMarquee) {

                     $oldUserMarquee = new OldUserMarquee();
                     $oldUserMarquee->fill($previousMarquee->toArray());
                     $oldUserMarquee->save();
                 }


                 $marquee = UserMarquee::first() ?? new UserMarquee();
                 $marquee->fill($validated);
                 $marquee->save();
             }

         }
         catch (Exception $ex){

             DB::rollback();
             return response()->json([
                 'message' => $ex->getMessage(),
                 'code' => $ex->getCode(),
                 'file' => $ex->getFile(),
                 'line' => $ex->getLine(),
             ], 500);
         }

        if (Auth::user()->ins === 2) return new RedirectResponse(route('biller.marquee.index'), ['flash_success' => "Marquee Saved successfully."]);
        else return new RedirectResponse(route('biller.dashboard'), ['flash_success' => "Marquee Saved successfully."]);
    }


    public function oldUserMarqueesTable()
    {

        $olds = OldUserMarquee::all();

        return Datatables::of($olds)
            ->editColumn('start', function ($model){

                return (new DateTime($model->start))->format('jS F Y | H:i') . ' hrs';
            })

            ->editColumn('end', function ($model){

                return (new DateTime($model->end))->format('jS F Y | H:i') . ' hrs';
            })
            ->make(true);
    }

    public function oldSuperAdminMarqueesTable()
    {

        $olds = OldSuperAdminMarquee::all();

        return Datatables::of($olds)
            ->editColumn('business', function ($model){

                $company = Company::find($model->business);

                return optional($company)->cname;
            })

            ->editColumn('start', function ($model){

                return (new DateTime($model->start))->format('jS F Y | H:i') . ' hrs';
            })

            ->editColumn('end', function ($model){

                return (new DateTime($model->end))->format('jS F Y | H:i') . ' hrs';
            })
            ->make(true);
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
    public function destroy($id)
    {

        if (Auth::user()->ins === 2) $marquee = SuperAdminMarquee::find($id);
        else $marquee = UserMarquee::where('ins', Auth::user()->ins)->first();

        if ($marquee) $marquee->delete();

        if (Auth::user()->ins === 2) return new RedirectResponse(route('biller.marquee.index'), ['flash_success' => "Marquee Deleted successfully."]);
        else return new RedirectResponse(route('biller.dashboard'), ['flash_success' => "Marquee Deleted successfully."]);
    }
}
