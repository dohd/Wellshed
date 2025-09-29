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

namespace App\Http\Controllers\Focus\customer_enrollment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Jobs\NotifyReferrer;
use App\Models\Company\Company;
use App\Models\Company\CompanyCommissionDetail;
use App\Models\customer\Customer;
use App\Models\customer_enrollment\CustomerEnrollment;
use App\Models\customer_enrollment\CustomerEnrollmentItem;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Repositories\Focus\customer_enrollment\CustomerEnrollmentRepository;

/**
 * CustomerEnrollmentsController
 */
class CustomerEnrollmentsController extends Controller
{
    /**
     * variable to store the repository object
     * @var CustomerEnrollmentRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param CustomerEnrollmentRepository $repository ;
     */
    public function __construct(CustomerEnrollmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.customer_enrollments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::get(['id', 'company']);
        return view('focus.customer_enrollments.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        $input['ins'] = auth()->user()->ins;
        try {
            //Create the model using repository create method
            $this->repository->create($input);
        } catch (\Throwable $th) {
            dd($th);
            //throw $th;
            return errorHandler('Error Creating Customer enrollments', $th);
        }
        //return with successfull message
        return new RedirectResponse(route('biller.customer_enrollments.index'), ['flash_success' => 'Customer Enrollment Creating Successfully!!']);
    }

    /**
     * Show the form for editing the specified resource.e
     */
    public function edit(CustomerEnrollment $customer_enrollment)
    {
        return view('focus.customer_enrollments.edit', compact('customer_enrollment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(Request $request, CustomerEnrollment $customer_enrollment)
    {
        //Input received from the request
        $input = $request->except(['_token', 'ins']);
        //Update the model using repository update method
        $this->repository->update($customer_enrollment, $input);
        //return with successfull message
        return new RedirectResponse(route('biller.customer_enrollments.index'), ['flash_success' => 'Customer Enrollment Updated Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(CustomerEnrollment $customer_enrollment)
    {
        //Calling the delete method on repository
        $this->repository->delete($customer_enrollment);
        //returning with successfull message
        return new RedirectResponse(route('biller.customer_enrollments.index'), ['flash_success' => 'Customer Enrollment Deleted Successfully!!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(CustomerEnrollment $customer_enrollment)
    {

        //returning with successfull message
        return new ViewResponse('focus.customer_enrollments.view', compact('customer_enrollment'));
    }

    public function get_redeemable_codes(Request $request)
    {
        $redeemable_code = $request->redeemable_code;

        $reservation = ThirdPartiesPromoCodeReservation::with(['promoCode' => function ($q) {
            $q->withoutGlobalScopes(['ins']); // 👈 override here if needed
        }])
            ->where('status', 'reserved')
            ->where('redeemable_code', $redeemable_code)
            ->first()
            ??
            CustomersPromoCodeReservation::with(['promoCode' => function ($q) {
                $q->withoutGlobalScopes(['ins']);
            }])
            ->where('status', 'reserved')
            ->where('redeemable_code', $redeemable_code)
            ->first()
            ??
            ReferralsPromoCodeReservation::with(['promoCode' => function ($q) {
                $q->withoutGlobalScopes(['ins']);
            }])
            ->where('status', 'reserved')
            ->where('redeemable_code', $redeemable_code)
            ->first();

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid redeemable code',
            ], 404);
        }

        $promoCode = $reservation->promoCode;

        // Load extra relationships depending on promo_type
        if ($promoCode->promo_type === 'product_categories') {
            $promoCode->load(['productCategories' => fn($q) => $q->withoutGlobalScopes(['ins'])]);
        } elseif ($promoCode->promo_type === 'specific_products') {
            $promoCode->load(['productVariations' => fn($q) => $q->withoutGlobalScopes(['ins'])]);
        }

        return response()->json([
            'success' => true,
            'reservation' => [
                'uuid'            => $reservation->uuid,
                'redeemable_code' => $reservation->redeemable_code,
                'status'          => $reservation->status,
                'expires_at'      => $reservation->expires_at,
                'customer_id'     => $reservation->customer_id,
                'name'            => $reservation->name,
                'email'           => $reservation->email,
                'phone'           => $reservation->phone,
                'organization'    => $reservation->organization,
                'message'         => $reservation->message,
            ],
            'promo_code' => [
                'id'               => $promoCode->id,
                'code'             => $promoCode->code,
                'promo_type'       => $promoCode->promo_type,
                'description'      => $promoCode->description,
                'description_promo' => $promoCode->description_promo,
                'valid_from'       => $promoCode->valid_from,
                'valid_until'      => $promoCode->valid_until,
                'usage_limit'      => $promoCode->usage_limit,
                'discount_type'    => $promoCode->discount_type,
                'discount_value'   => $promoCode->discount_value,
                'product_categories' => $promoCode->promo_type === 'product_categories'
                    ? $promoCode->productCategories->map(fn($c) => ['id' => $c->id, 'title' => $c->title])
                    : [],
                'products' => $promoCode->promo_type === 'specific_products'
                    ? $promoCode->productVariations->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
                    : [],
            ],
        ]);
    }

    public function change_status(Request $request, $customer_enrollment_id)
    {
        $customer_enrollment = CustomerEnrollment::findOrFail($customer_enrollment_id);

        $data = $request->only(['status', 'date', 'quote_amount', 'status_note']);
        $data['date'] = date_for_database($data['date']);
        $data['quote_amount'] = numberClean($data['quote_amount']);

        $customer_enrollment->update($data);

        if ($customer_enrollment->status === 'approved' && count($customer_enrollment->items) < 1) {
            // dd($customer_enrollment,$customer_enrollment->items);
            $reservation = $this->findReservationByUuid($customer_enrollment->reserve_uuid);

            if ($reservation) {
                $this->handleReservation($reservation, $customer_enrollment);
            }
        }

        return back()->with('flash_success', 'Status Changed Successfully!!');
    }

    /**
     * Try to find reservation from all possible models.
     */
    private function findReservationByUuid($uuid)
    {
        return ThirdPartiesPromoCodeReservation::where('uuid', $uuid)->first()
            ?? CustomersPromoCodeReservation::where('uuid', $uuid)->first()
            ?? ReferralsPromoCodeReservation::where('uuid', $uuid)->first();
    }

    /**
     * Handle reservation tiers logic.
     */
    private function handleReservation($reservation, $customer_enrollment)
    {
        if ($reservation->tier == 3) {
            $tier_3 = $reservation;
            $tier_2 = $reservation->referralReferer;
            $tier_1 = $tier_2->customerReferer ?? $tier_2->thirdPartyReferer ?? null;

            // dd($tier_1, $tier_2);
            if ($tier_1) {
                $this->notifyTier1($tier_1, $tier_2, $tier_3, 3, $customer_enrollment);
            }
            if ($tier_2) {

                $this->notifyTier2($tier_2, $tier_3, $customer_enrollment);
            }
        } elseif ($reservation->tier == 2) {
            $tier_1 = $reservation->customerReferer ?? $reservation->thirdPartyReferer ?? null;

            if ($tier_1) {
                $this->notifyTier1($tier_1, $reservation, null, 2, $customer_enrollment);
            }
        }elseif($reservation->tier == 1 && $customer_enrollment->is_review){
            $this->notifyZero($reservation, $customer_enrollment);
        }
    }

    private function notifyZero($tier_1, $customer_enrollment){
        $company = Company::find(auth()->user()->ins);
        $subject = "Notification to Referrer";
        $commission = $this->formatCommission($tier_1->promoCode, 'cash_back_1');
        $data[] = $this->prepareEnrollmentData($tier_1, $commission, $customer_enrollment, 'cash_back_1');

        $message = "Your referral code, has been redeemed at {$company->cname}. 
                    We will notify you once invoicing is complete so you can expect your commission.";

        if($tier_1->promoCode->company_commission > 0){
            $isPercentage = $tier_1->promoCode->total_commission_type === 'percentage';
            $actualCommission = $isPercentage
            ? ($tier_1->promoCode->company_commission_percent / 100) * $customer_enrollment->quote_amount
            : $tier_1->promoCode->company_commission_amount;
            $company_commission_details = CompanyCommissionDetail::find(1);
            $data[] = [
                'customer_enrollment_id' => $customer_enrollment->id,
                'name' => $company_commission_details->name,
                'email' => $company_commission_details->email,
                'phone' => $company_commission_details->phone,
                'redeemable_code' => 'COMPANY',
                'promo_code_id' => $tier_1->promoCode->promo_code_id,
                'reservation_uuid' => '',
                'raw_commission' => $actualCommission,   // raw configured value
                'commission' => $actualCommission,                                  // computed commission
                'actual_commission' => $actualCommission,                     // NEW: based on quote_amount
                'commission_type' => $tier_1->promoCode->total_commission_type,
                'quote_amount' => $customer_enrollment->quote_amount,
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ];
        }
        CustomerEnrollmentItem::insert($data);
        NotifyReferrer::dispatch(auth()->user()->ins, $tier_1, $message, $subject);
    }

    /**
     * Notify Tier 1 Referrer
     */
    private function notifyTier1($tier_1, $tier_2, $tier_3, $tier, $customer_enrollment)
    {
        $company = Company::find(auth()->user()->ins);
        $subject = "Notification to Referrer";

        $data = [];
        if ($tier == 2) {
            $commission = $this->formatCommission($tier_1->promoCode, 'cash_back_1');
            $data[] = $this->prepareEnrollmentData($tier_1, $commission, $customer_enrollment, 'cash_back_1');

            $message = "Your referral, {$tier_2->name}, has redeemed their referral code at {$company->cname}. 
                        We will notify you once invoicing is complete so you can expect your commission.";
        } else if ($tier == 3) {
            $commission = $this->formatCommission($tier_1->promoCode, 'cash_back_3');
            $data[] = $this->prepareEnrollmentData($tier_1, $commission, $customer_enrollment, 'cash_back_3');
            $message = "Your referral, {$tier_3->name}, referred by {$tier_2->name}, 
                        has redeemed their referral code at {$company->cname}. 
                        We will notify you once invoicing is complete so you can expect your commission.";
        }

        if($tier_1->promoCode->company_commission > 0){
            $isPercentage = $tier_1->promoCode->total_commission_type === 'percentage';
            $actualCommission = $isPercentage
            ? ($tier_1->promoCode->company_commission_percent / 100) * $customer_enrollment->quote_amount
            : $tier_1->promoCode->company_commission_amount;
            $company_commission_details = CompanyCommissionDetail::find(1);
            $data[] = [
                'customer_enrollment_id' => $customer_enrollment->id,
                'name' => $company_commission_details->name,
                'email' => $company_commission_details->email,
                'phone' => $company_commission_details->phone,
                'redeemable_code' => 'COMPANY',
                'promo_code_id' => $tier_1->promoCode->promo_code_id,
                'reservation_uuid' => '',
                'raw_commission' => $actualCommission,   // raw configured value
                'commission' => $actualCommission,                                  // computed commission
                'actual_commission' => $actualCommission,                     // NEW: based on quote_amount
                'commission_type' => $tier_1->promoCode->total_commission_type,
                'quote_amount' => $customer_enrollment->quote_amount,
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ];
        }

        CustomerEnrollmentItem::insert($data);
        NotifyReferrer::dispatch(auth()->user()->ins, $tier_1, $message, $subject);
    }

    /**
     * Notify Tier 2 Referrer
     */
    private function notifyTier2($tier_2, $tier_3, $customer_enrollment)
    {
        $commission = $this->formatCommission($tier_2->promoCode, 'cash_back_2');
        $data = $this->prepareEnrollmentData($tier_2, $commission, $customer_enrollment, 'cash_back_2');

        CustomerEnrollmentItem::create($data);

        $company = Company::find(auth()->user()->ins);
        $subject = "Notification to Referrer";
        $message = "Your referral, {$tier_3->name}, has redeemed their referral code at {$company->cname}. 
                    We will notify you once invoicing is complete so you can expect your commission.";

        NotifyReferrer::dispatch(auth()->user()->ins, $tier_2, $message, $subject);
    }

    /**
     * Calculate commission based on total_commission_type and field.
     */
    private function formatCommission($promoCode, $field, $quoteAmount = null)
    {
        if ($promoCode->total_commission_type === 'percentage') {
            $fieldName = $field . '_percent';
            $percentValue = $promoCode->$fieldName;

            // If quote amount is provided, calculate actual commission from it
            if ($quoteAmount !== null) {
                return ($percentValue / 100) * $quoteAmount;
            }

            // Otherwise calculate from total_commission
            return ($percentValue / 100) * $promoCode->total_commission;
        }

        if ($promoCode->total_commission_type === 'fixed') {
            $fieldName = $field . '_amount';
            return $promoCode->$fieldName;
        }

        return 0;
    }

    /**
     * Prepare enrollment item data
     */
    private function prepareEnrollmentData($referral, $commission, $customer_enrollment, $field)
    {
        $isPercentage = $referral->promoCode->total_commission_type === 'percentage';
        $commissionField = $isPercentage ? $field . '_percent' : $field . '_amount';

        // Calculate actual commission based on enrollment quote amount if percentage
        $actualCommission = $isPercentage
            ? ($referral->promoCode->$commissionField / 100) * $customer_enrollment->quote_amount
            : $referral->promoCode->$commissionField;

        return [
            'customer_enrollment_id' => $customer_enrollment->id,
            'name' => $referral->name,
            'email' => $referral->email,
            'phone' => $referral->phone,
            'redeemable_code' => $referral->redeemable_code,
            'promo_code_id' => $referral->promo_code_id,
            'reservation_uuid' => $referral->uuid,
            'raw_commission' => $referral->promoCode->$commissionField,   // raw configured value
            'commission' => $commission,                                  // computed commission
            'actual_commission' => $actualCommission,                     // NEW: based on quote_amount
            'commission_type' => $referral->promoCode->total_commission_type,
            'quote_amount' => $customer_enrollment->quote_amount,
            'ins' => auth()->user()->ins,
            'user_id' => auth()->user()->id,
        ];
    }

    public function change_payment_status(Request $request)
    {
        $ids = $request->input('ids', []);
        $customer_enrollment_id = $request->customer_enrollment_id;

        // Do something with selected IDs
        CustomerEnrollmentItem::whereIn('id', $ids)->update([
            'payment_status' => 'paid',
            'payment_date' => date('Y-m-d'),
        ]);
        $customer_enrollment = CustomerEnrollment::find($customer_enrollment_id);
        //update the payment status on Enrollment parent
        $total_items = count($customer_enrollment->items);
        $items = $customer_enrollment->items()->where('payment_status', 'paid')->get();
        $paid_items = count($items);
        if ($total_items > $paid_items && $paid_items > 0) {
            $customer_enrollment->update(['payment_status' => 'partial']);
        } elseif ($total_items == $paid_items) {
            $customer_enrollment->update(['payment_status' => 'paid']);
        }

        return response()->json(['success' => true, 'message' => 'Updated successfully']);
    }

    public function notify_referrers(Request $request, $customer_enrollment_id)
    {
        $customer_enrollment = CustomerEnrollment::find($customer_enrollment_id);
        $customer_enrollment->notification_status = $request->notification_status;
        $customer_enrollment->payment_status = $request->payment_status;
        $customer_enrollment->payment_note = $request->payment_note;
        $customer_enrollment->payment_date = date_for_database($request->payment_date);
        $customer_enrollment->update();
        if ($customer_enrollment->notification_status === 'yes' && $customer_enrollment->payment_status !== 'pending') {
            $this->sendNotification($customer_enrollment);
        }
        return back()->with('flash_success', 'Notification Sent Successfully!!');
    }
    private function getReferrer($entity)
    {
        return $entity->customerReferer ?? $entity->thirdPartyReferer ?? null;
    }

    private function sendNotification($customer_enrollment)
    {
        if ($customer_enrollment->items->isNotEmpty()) {
            $company = Company::find(auth()->user()->ins);

            foreach ($customer_enrollment->items as $item) {
                $reservation = $this->findReservationByUuid($item->reservation_uuid);

                if ($reservation && $reservation->tier == 2) {
                    $tier_2 = $reservation;
                    $tier_1 = $this->getReferrer($tier_2);

                    // Buyer (tier 3)
                    $tier_3 = ReferralsPromoCodeReservation::where('referer_uuid', $tier_2->uuid)->first();

                    // ✅ Notify Tier 1 (indirect referral)
                    if ($tier_1 && $tier_3) {
                        $find_item_tier_1 = $customer_enrollment->items()
                            ->where('reservation_uuid', $tier_1->uuid)
                            ->first();

                        $messageTier1 = sprintf(
                            "Hello %s, your referral %s (referred by %s) has successfully made a purchase at %s. "
                                . "Please expect your commission of %s to be sent to your Mpesa number (%s) within 7 days.",
                            $tier_1->name,
                            $tier_3->name,
                            $tier_2->name ?? 'unknown',
                            $company->cname,
                            $find_item_tier_1->actual_commission ?? '0',
                            $tier_1->phone
                        );

                        NotifyReferrer::dispatch($company->id, $tier_1, $messageTier1, "Referral Commission Pending");
                    }

                    // ✅ Notify Tier 2 (direct referral)
                    if ($tier_2 && $tier_3) {
                        $find_item_tier_2 = $customer_enrollment->items()
                            ->where('reservation_uuid', $tier_2->uuid)
                            ->first();

                        $messageTier2 = sprintf(
                            "Hello %s, your referral %s has successfully made a purchase at %s. "
                                . "Please expect your commission of %s to be sent to your Mpesa number (%s) within 7 days.",
                            $tier_2->name,
                            $tier_3->name,
                            $company->cname,
                            $find_item_tier_2->actual_commission ?? '0',
                            $tier_2->phone
                        );

                        NotifyReferrer::dispatch($company->id, $tier_2, $messageTier2, "Referral Commission Pending");
                    }
                    // dd($messageTier1, $messageTier2);
                }
            }
        }
    }
}
