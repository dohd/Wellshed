<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\commission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\commission\Commission;
use App\Models\commission\CommissionItem;
use App\Models\Company\Company;
use App\Models\customer_enrollment\CustomerEnrollmentItem;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\PromotionalCode;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Repositories\Focus\commission\CommissionRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * CommissionsController
 */
class CommissionsController extends Controller
{
    /**
     * variable to store the repository object
     * @var CommissionRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CommissionRepository $repository ;
     */
    public function __construct(CommissionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\department\ManageDepartmentRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.commissions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\CreateResponse
     */
    public function create(Request $request)
    {
        return view('focus.commissions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $data = $request->only(['date', 'title', 'total']);
        //line items
        $data_items = $request->only(['name', 'phone', 'raw_commision', 'reserve_uuid',
         'commission_type', 'actual_commission', 'invoice_amount', 'quote_id', 'quote_amount', 'invoice_id',
         'customer_enrollment_item_id'
        ]);
        $data_items = modify_array($data_items);
        try {
            $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Commission', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.commissions.index'), ['flash_success' => 'Commission Created Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\department\Department $department
     * @param EditDepartmentRequestNamespace $request
     * @return \App\Http\Responses\Focus\department\EditResponse
     */
    public function edit(Commission $commission)
    {
        return view('focus.commissions.edit', compact('commission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, Commission $commission)
    {
        $data = $request->only(['date', 'title', 'total']);
        //line items
        $data_items = $request->only(['name', 'phone', 'raw_commision', 'reserve_uuid',
         'commission_type', 'actual_commission', 'invoice_amount', 'invoice_id', 'quote_id',
          'quote_amount', 'id','customer_enrollment_item_id']);
        $data_items = modify_array($data_items);
        try {
            $this->repository->update($commission, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Commission', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.commissions.index'), ['flash_success' => 'Commission Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Commission $commission)
    {
        //Calling the delete method on repository
        $this->repository->delete($commission);
        //returning with successfull message
        return new RedirectResponse(route('biller.commissions.index'), ['flash_success' => 'Commission Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteDepartmentRequestNamespace $request
     * @param App\Models\department\Department $department
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Commission $commission)
    {

        //returning with successfull message
        return new ViewResponse('focus.commissions.view', compact('commission'));
    }

    public function internal_commission()
    {
        $promotional_codes = PromotionalCode::all();
        return view('focus.commissions.internal_commission', compact('promotional_codes'));
    }

    public function get_internal_commission(Request $request)
    {
        $q = CustomerEnrollmentItem::query();
        $q->when(request('promotional_code'),function($q){
            $q->where('promo_code_id', request('promotional_code'));
        });
        $q->when(request('payment_status'), function($q){
            $q->whereHas('commission_item', function($q){
                $q->whereHas('commission.bill', fn($q) => $q->where('status',request('payment_status')));
            });
        });
        $core = $q->get();
        return DataTables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($r) {
                $commission = $r->commission_item;
                $cust_enrollment = $r->customer_enrollment;
                if ($commission) {
                    return '<input checked disabled type="checkbox" class="select-row" value="' . e($r['id']) . '">';
                }elseif ($cust_enrollment && $cust_enrollment->payment_status === 'pending') {
                    return '<input checked disabled type="checkbox" class="select-row" value="' . e($r['id']) . '">';
                }
                return '<input type="checkbox" class="select-row" value="' . e($r['id']) . '">';
            })

          ->addColumn('status', function ($r) {
                $item = $r->commission_item;

                if (!$item) {
                    return "<span>pending</span>"; // no commission item, no status
                }

                $status = $item->commission->bill->status ?? '';

                $styles = '';
                if (strtolower(trim($status)) === 'paid') {
                    $styles = 'color: green; font-weight: bold;';
                } elseif (!empty($status)) {
                    $styles = 'color: red; font-weight: bold;';
                }

                return "<span style='{$styles}'>" . e($status) . "</span>";
            })
            ->addColumn('name', fn($r) => $r['name'])
            ->addColumn('email', fn($r) => $r['email'])
            ->addColumn('phone', fn($r) => $r['phone'])
            ->addColumn('tier', function($r){
                $reservation = ThirdPartiesPromoCodeReservation::where('uuid', $r->reservation_uuid)->first() ??
                CustomersPromoCodeReservation::where('uuid', $r->reservation_uuid)->first() ??
                ReferralsPromoCodeReservation::where('uuid', $r->reservation_uuid)->first();
                return $reservation->tier ?? 0;
            })
            ->addColumn('redeemable_code', fn($r) => $r['redeemable_code'])
            ->addColumn('commision', fn($r) => $r['actual_commission'])
            ->rawColumns(['checkbox', 'status'])
            ->make(true);
    }

    public function create_commision_pay(Request $request)
    {
        $uuids = explode(',', $request->input('ids'));
        $models = [
            CustomerEnrollmentItem::class,
        ];

        $reservations = collect();

       foreach ($models as $model) {
            $filtered = $model::whereIn('id', $uuids)
                ->get();

            $reservations = $reservations->merge($filtered);
        }
        $reserves = [];
        foreach ($reservations as $reservation) {
            $reserves[] = $this->referrerData($reservation);
        }
        return view('focus.commissions.create', compact('reserves'));
    }

    private function referrerData($referrer)
    {
        $reservation = ThirdPartiesPromoCodeReservation::where('uuid', $referrer->reservation_uuid)->first() ??
                CustomersPromoCodeReservation::where('uuid', $referrer->reservation_uuid)->first() ??
                ReferralsPromoCodeReservation::where('uuid', $referrer->reservation_uuid)->first();
        return [
            'customer_enrollment_item_id' => $referrer->id,
            'promo_code_id' => $referrer->promo_code_id,
            'uuid' => $referrer->reservation_uuid,
            'name' => $referrer->name,
            'email' => $referrer->email,
            'phone' => $referrer->phone,
            'tier' => $reservation->tier ?? 0,
            'redeemable_code' => $referrer->redeemable_code,
            'raw_commision' => $referrer->raw_commission,
            'commission' => $referrer->commision,
            'actual_commission' => $referrer->actual_commission,
            'commision_type' => $referrer->commission_type,
            'quote_id' => $referrer->quote_id,
            'invoice_id' => $referrer->invoice_id,
            'quote_amount' => $referrer->quote_amount,
            'total' => $referrer->quote_amount,
        ];
    }

    public function all_commission()
    {
        $promotional_codes = PromotionalCode::all();
        $companies = Company::all();
        return view('focus.commissions.all_commission', compact('promotional_codes','companies'));
    }

    public function get_all_commission(Request $request)
    {
        $q = CustomerEnrollmentItem::withoutGlobalScopes(); // main model

        $q->when(request('promotional_code'), function($q) {
            $q->where('promo_code_id', request('promotional_code'));
        });

        $q->when(request('payment_status'), function($q) {
            $q->whereHas('commission_item', function($q) {
                $q->withoutGlobalScopes() // CommissionItem
                ->whereHas('commission', function($q) {
                    $q->withoutGlobalScopes() // Commission
                        ->whereHas('bill', function($q) {
                            $q->withoutGlobalScopes() // Bill
                            ->where('status', request('payment_status'));
                        });
                });
            });
        });

        $q->when(request('tenant_id'), function($q){
            $q->where('ins', request('tenant_id'));
        });

        $core = $q->get();

        return DataTables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($r) {
                $commission = $r->commission_item;
                $cust_enrollment = $r->customer_enrollment;
                if ($commission) {
                    return '<input checked disabled type="checkbox" class="select-row" value="' . e($r['id']) . '">';
                }elseif ($cust_enrollment && $cust_enrollment->payment_status === 'pending') {
                    return '<input checked disabled type="checkbox" class="select-row" value="' . e($r['id']) . '">';
                }
                return '<input type="checkbox" class="select-row" value="' . e($r['id']) . '">';
            })

            ->addColumn('status', function ($r) {
                $item = $r->commission_item()->withoutGlobalScopes()->first();

                if (!$item) {
                    return "<span>pending</span>"; // no commission item, no status
                }

                // Re-fetch commission_item with relations, ignoring global scopes
                $item = CommissionItem::withoutGlobalScopes()
                    ->with(['commission' => function ($q) {
                        $q->withoutGlobalScopes()->with(['bill' => fn($q) => $q->withoutGlobalScopes()]);
                    }])
                    ->find($item->id);

                $status = $item->commission->bill->status ?? '';

                $styles = '';
                if (strtolower(trim($status)) === 'paid') {
                    $styles = 'color: green; font-weight: bold;';
                } elseif (!empty($status)) {
                    $styles = 'color: red; font-weight: bold;';
                }

                return "<span style='{$styles}'>" . e($status) . "</span>";
            })

            ->addColumn('name', fn($r) => $r['name'])
            ->addColumn('email', fn($r) => $r['email'])
            ->addColumn('phone', fn($r) => $r['phone'])
            ->addColumn('tier', function($r){
                $reservation = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->where('uuid', $r->reservation_uuid)->first() ??
                CustomersPromoCodeReservation::withoutGlobalScopes()->where('uuid', $r->reservation_uuid)->first() ??
                ReferralsPromoCodeReservation::withoutGlobalScopes()->where('uuid', $r->reservation_uuid)->first();
                return $reservation->tier ?? 0;
            })
            ->addColumn('redeemable_code', fn($r) => $r['redeemable_code'])
            ->addColumn('commision', fn($r) => $r['actual_commission'])
            ->rawColumns(['checkbox', 'status'])
            ->make(true);
    }
}
