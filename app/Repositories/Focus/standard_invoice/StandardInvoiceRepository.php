<?php

namespace App\Repositories\Focus\standard_invoice;

use App\Exceptions\GeneralException;
use App\Http\Responses\RedirectResponse;
use App\Jobs\NotifyReferrer;
use App\Models\Company\Company;
use App\Models\customer_enrollment\CustomerEnrollmentItem;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\InvoicePromotionalDiscountData;
use App\Models\invoice\Invoice;
use App\Models\items\InvoiceItem;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use DB;
use Exception;
use Illuminate\Validation\ValidationException;

/**
 * Class InvoiceRepository.
 */
class StandardInvoiceRepository extends BaseRepository
{
    use Accounting;

    /**
     * Associated Repository Model.
     */
    const MODEL = Invoice::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws \Exception
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        $data = $input['data'];
        $data['is_standard'] = 1;
        $duedate = $data['invoicedate'] . ' + ' . $data['validity'] . ' days';
        $data['invoiceduedate'] = date_for_database($duedate);
        foreach ($data as $key => $val) {
            if ($key == 'invoicedate') $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'taxable', 'tax', 'total_promo_discount', 'total_promo_discounted_tax']))
                $data[$key] = numberClean($val);
        }
        $tid = Invoice::max('tid');
        if (@$data['tid'] && $data['tid'] <= $tid) $data['tid'] = $tid + 1;
        //  forex values
        $fx_rate = @$data['fx_curr_rate'];
        if ($fx_rate > 1) {
            $data = array_replace($data, [
                'fx_taxable' => round($data['taxable'] * $fx_rate, 4),
                'fx_subtotal' => round($data['subtotal'] * $fx_rate, 4),
                'fx_tax' => round($data['tax'] * $fx_rate, 4),
                'fx_total' => round($data['total'] * $fx_rate, 4),
            ]);
        }

        DB::beginTransaction();

        // create standard invoice
        $result = Invoice::create($data);
        $result->update(['currency_id' => $result->customer->currency_id]);

        // create invoice items
        $data_items = modify_array($input['data_items']);
        $data_items = array_filter($data_items, fn($v) => numberClean($v['product_amount']) != 0);
        if (!$data_items) throw ValidationException::withMessages(['Line item totals required!']);
        foreach ($data_items as $k => $item) {
            foreach ($item as $j => $value) {
                if (in_array($j, ['tax_rate', 'product_qty', 'product_price', 'product_tax', 'product_subtotal', 'product_amount'])) {
                    $item[$j] = floatval(str_replace(',', '', $value));
                }
            }

            // forex values
            $fx_rate = $result->fx_curr_rate;
            if ($fx_rate > 1) {
                $item = array_replace($item, [
                    'fx_curr_rate' => $fx_rate,
                    'fx_product_tax' => round($item['product_tax'] * $fx_rate, 4),
                    'fx_product_price' => round($item['product_price'] * $fx_rate, 4),
                    'fx_product_subtotal' => round($item['product_subtotal'] * $fx_rate, 4),
                    'fx_product_amount' => round($item['product_amount'] * $fx_rate, 4),
                ]);
            }
            $data_items[$k] = array_replace($item, ['invoice_id' => $result->id]);
        }
        InvoiceItem::insert($data_items);

        /** accounting */
        $this->post_invoice($result);

        if ($result) {
            $promoDiscountData = $input['promoDiscountData'] ?? [];

            if (!empty($promoDiscountData)) {

                foreach ($promoDiscountData as $d) {
                    $promoDiscountData = new InvoicePromotionalDiscountData();
                    $promoDiscountData->invoice_id = $result->id;
                    $promoDiscountData->fill(collect($d)->toArray());
                    $promoDiscountData->save();
                }
                $reservation = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($result->reservation) ??
                    CustomersPromoCodeReservation::withoutGlobalScopes()->find($result->reservation) ??
                    ReferralsPromoCodeReservation::withoutGlobalScopes()->find($result->reservation);
                $reservation->status = 'used';
                $this->reservation_type($reservation, $result);
                $reservation->save();

                $promoCode = $reservation->promoCode;
                $promoCode->increment('used_count');
            }
            DB::commit();
            return $result;
        }
    }

    public function reservation_type($reservation, $invoice)
    {
        $promo_code = $reservation->promoCode;

        if ($reservation->tier == 3) {
            $tier_2 = $reservation->referralReferer;
            $tier_1 = $this->getReferrer($tier_2);

            $this->notifyTier2WithOptionalTier1($tier_2, $tier_1, $reservation, $promo_code, $invoice);
        } elseif ($reservation->tier == 2) {
            $tier_1 = $this->getReferrer($reservation);

            $this->notifyTier1($tier_1, $reservation, $promo_code, $invoice);
        }
    }

    private function getReferrer($entity)
    {
        return $entity->customerReferer ?? $entity->thirdPartyReferer ?? null;
    }

    private function notifyTier1($referrer, $referee, $promo_code, $invoice)
    {
        if (!$referrer) return;

        // Helper to fetch and update enrollment item
        $getEnrollmentItem = function ($redeemableCode, $invoiceId, $quoteId = null) {
            $query = CustomerEnrollmentItem::where('redeemable_code', $redeemableCode);
            if ($quoteId) {
                $query->where('quote_id', $quoteId);
            }
            $item = $query->first();
            if ($item) {
                $item->invoice_id = $invoiceId;
                $item->update();
            }
            return $item;
        };

        // Update referrer item
        $customer_item = $getEnrollmentItem($referrer->redeemable_code, $invoice->id);

        if (!$customer_item) return; // stop if referrer has no enrollment

        // Update COMPANY item using the same quote_id
        $item = $getEnrollmentItem('COMPANY', $invoice->id, $customer_item->quote_id);

        $company = Company::find(auth()->user()->ins);
        $subject = "Notification to Referrer";

        $message = sprintf(
            "Your referral, %s, has successfully made a purchase from %s. Please expect your commission of %s to be sent to your Mpesa number (%s) within 7 days.",
            $referee->name,
            $company->cname,
            $customer_item->actual_commission ?? 0,
            $referrer->phone
        );

        NotifyReferrer::dispatch(auth()->user()->ins, $referrer, $message, $subject);
    }



    private function notifyTier2WithOptionalTier1($tier_2, $tier_1, $tier_3, $promo_code, $invoice)
    {
        $company = Company::find(auth()->user()->ins);
        $subject = "Notification to Referrer";

        // Helper to fetch and update enrollment item
        $getEnrollmentItem = function ($tier) use ($invoice) {
            if (!$tier) return null;
            $item = CustomerEnrollmentItem::where('redeemable_code', $tier->redeemable_code)->first();
            if ($item) {
                $item->invoice_id = $invoice->id;
                $item->update();
            }
            return $item;
        };

        $tier_2_item = $getEnrollmentItem($tier_2);
        $tier_1_item = $getEnrollmentItem($tier_1);
        $company_commission = CustomerEnrollmentItem::where('redeemable_code', 'COMPANY')->where('quote_id', $tier_1_item->quote_id)->first();
        if ($company_commission && $tier_1_item) {
            $company_commission->invoice_id = $invoice->id;
            $company_commission->update();
        }

        $tiers = [
            [
                'user'    => $tier_2,
                'amount'  => $tier_2_item->actual_commission,
                'message' => function ($tier) use ($tier_3, $company, $tier_2_item) {
                    return sprintf(
                        "Your referral, %s, has successfully made a purchase from %s. Please expect your commission of %s to be sent to your Mpesa number (%s) within 7 days.",
                        $tier_3->name,
                        $company->cname,
                        $tier_2_item->actual_commission ?? 0,
                        $tier->phone
                    );
                },
            ],
            [
                'user'    => $tier_1,
                'amount'  => $tier_1_item->actual_commission,
                'message' => function ($tier) use ($tier_2, $tier_3, $company, $tier_1_item) {
                    return sprintf(
                        "Your referral, %s, referred by %s, has successfully made a purchase from %s. Please expect your commission of %s to be sent to your Mpesa number (%s) within 7 days.",
                        $tier_3->name,
                        $tier_2->name ?? 'unknown',
                        $company->cname,
                        $tier_1_item->actual_commission ?? 0,
                        $tier->phone
                    );
                },
            ],
        ];

        foreach ($tiers as $tierInfo) {
            if ($tierInfo['user'] && $tierInfo['amount']) {
                $message = $tierInfo['message']($tierInfo['user']);
                NotifyReferrer::dispatch(auth()->user()->ins, $tierInfo['user'], $message, $subject);
            }
        }
    }


    private function formatCommission($promoCode, $field, $quoteAmount)
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
}
