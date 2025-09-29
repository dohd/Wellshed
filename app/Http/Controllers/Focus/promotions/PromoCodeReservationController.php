<?php

namespace App\Http\Controllers\Focus\promotions;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Jobs\SendBulkSms;
use App\Models\Access\User\User;
use App\Models\commission\CommissionItem;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\lead\Lead;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\PromotionalCode;
use App\Models\promotions\PromotionalCodeProduct;
use App\Models\promotions\PromotionalCodeProductCategory;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\quote\Quote;
use App\Models\send_email\SendEmail;
use App\Models\send_sms\SendSms;
use App\Repositories\Focus\general\RosemailerRepository;
use App\Repositories\Focus\general\RosesmsRepository;
use App\Repositories\ReferralChainService;
use Closure;
use data;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Schema;
use Yajra\DataTables\Facades\DataTables;

class PromoCodeReservationController extends Controller
{

    use ReferralChainService;
    /**
     * Display a listing of the resource.
     *
     * @throws \DateMalformedStringException
     */
    public function index(Request $request)
    {
        if (!access()->allow('manage-reserve-promo-codes')) return response('', 403);

        if ($request->ajax()) {

            $customerReservations = CustomersPromoCodeReservation::orderBy('reserved_at', 'desc')
                ->when(request('statusFilter'), fn($query) => $query->where('status', request('statusFilter')))
                ->when(request('reservedDateFilter'), fn($query, $status) => $query->whereDate('reserved_at', request('reservedDateFilter')))
                ->when(request('expiryDateFilter'), fn($query, $status) => $query->whereDate('expires_at', request('expiryDateFilter')))
                ->get()
                ->map(function ($res) {

                    $products = '';
                    if (count($res->promoCode->productVariations) > 0) {

                        $no = 1;
                        foreach ($res->promoCode->productVariations as $product) $products .= '<span> <b>' . $no++ . '.) </b>' . $product->name . '</span><br>';
                    }

                    $categories = '';
                    if (count($res->promoCode->productCategories) > 0) {

                        $no = 1;
                        foreach ($res->promoCode->productCategories as $cat) $categories .= '<span> <b>' . $no++ . '.) </b>' . $cat->title . '</span><br>';
                    }

                    if ($res->status === 'reserved') $status = '<span class="badge badge-warning">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'used') $status = '<span class="badge badge-success">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'expired') $status = '<span class="badge badge-danger">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'cancelled') $status = '<span class="badge badge-light">' . strtoupper($res->status) . '</span>';

                    $details = '<span>' . @$res->customer->company . '</span><br>' .
                        '<span> <b>Phone</b>: ' . $res->phone . '</span><br>' .
                        '<span> <b>Email</b>: ' . $res->email . '</span><br>';

                    $show = '<a href="' . route('biller.show-reserve-customer-promo-code', $res->uuid) . '" class="btn btn-secondary round mr-1">View</a>';
                    $edit = '<a href="' . route('biller.edit-reserve-customer-promo-code', $res->uuid) . '" class="btn btn-secondary round mr-1">Edit</a>';


                    return [

                        'customer' => $details,
                        'code' => $res->promoCode->code,
                        'products' => $products,
                        'categories' => $categories,
                        'created_at' => dateFormat($res->created_at),
                        'reserved_at' => (new DateTime($res->reserved_at))->format('d/m/Y, g:iA'),
                        'expires_at' => (new DateTime($res->expires_at))->format('d/m/Y, g:iA'),
                        'message' => $res->message,
                        'status' => $status,
                        'reserved_by' => optional($res->reserver)->fullname,
                        'referred_by' => '<i><b>Non-referral</b></i>',
                        'action' => $show . $edit,
                    ];
                });

            $thirdPartyReservations = ThirdPartiesPromoCodeReservation::orderBy('reserved_at', 'desc')
                ->when(request('statusFilter'), fn($query) => $query->where('status', request('statusFilter')))
                ->when(request('reservedDateFilter'), fn($query, $status) => $query->whereDate('reserved_at', request('reservedDateFilter')))
                ->when(request('expiryDateFilter'), fn($query, $status) => $query->whereDate('expires_at', request('expiryDateFilter')))
                ->get()
                ->map(function ($res) {

                    $products = '';
                    if (count($res->promoCode->productVariations) > 0) {

                        $no = 1;
                        foreach ($res->promoCode->productVariations as $product) $products .= '<span> <b>' . $no++ . '.) </b>' . $product->name . '</span><br>';
                    }

                    $categories = '';
                    if (count($res->promoCode->productCategories) > 0) {

                        $no = 1;
                        foreach ($res->promoCode->productCategories as $cat) $categories .= '<span> <b>' . $no++ . '.) </b>' . $cat->title . '</span><br>';
                    }

                    if ($res->status === 'reserved') $status = '<span class="badge badge-warning">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'used') $status = '<span class="badge badge-success">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'expired') $status = '<span class="badge badge-danger">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'cancelled') $status = '<span class="badge badge-light">' . strtoupper($res->status) . '</span>';

                    $details = '<span>' . $res->name . '</span><br>' .
                        '<span> <b>Organization</b>: ' . $res->organization . '</span><br>' .
                        '<span> <b>Phone</b>: ' . $res->phone . '</span><br>' .
                        '<span> <b>Email</b>: ' . $res->email . '</span><br>';

                    $show = '<a href="' . route('biller.show-reserve-3p-promo-code', $res->uuid) . '" class="btn btn-secondary round mr-1">View</a>';
                    $edit = '<a href="' . route('biller.edit-reserve-3p-promo-code', $res->uuid) . '" class="btn btn-secondary round mr-1">Edit</a>';

                    return [

                        'customer' => $details,
                        'code' => $res->promoCode->code,
                        'products' => $products,
                        'categories' => $categories,
                        'created_at' => dateFormat($res->created_at),
                        'reserved_at' => (new DateTime($res->reserved_at))->format('d/m/Y, g:iA'),
                        'expires_at' => (new DateTime($res->expires_at))->format('d/m/Y, g:iA'),
                        'message' => $res->message,
                        'status' => $status,
                        'reserved_by' => optional($res->reserver)->fullname,
                        'referred_by' => '<i><b>Non-referral</b></i>',
                        'action' => $show . $edit,
                    ];
                });


            $referralReservations = ReferralsPromoCodeReservation::orderBy('reserved_at', 'desc')
                ->when(request('statusFilter'), fn($query) => $query->where('status', request('statusFilter')))
                ->when(request('reservedDateFilter'), fn($query, $status) => $query->whereDate('reserved_at', request('reservedDateFilter')))
                ->when(request('expiryDateFilter'), fn($query, $status) => $query->whereDate('expires_at', request('expiryDateFilter')))
                ->get()
                ->map(function ($res) {


                    $promoCode = PromotionalCode::find($res->promo_code_id);

                    $products = '';
                    if (count($promoCode->productVariations) > 0) {

                        $no = 1;
                        foreach ($promoCode->productVariations as $product) $products .= '<span> <b>' . $no++ . '.) </b>' . $product->name . '</span><br>';
                    }

                    $categories = '';
                    if (count($promoCode->productCategories) > 0) {

                        $no = 1;
                        foreach ($promoCode->productCategories as $cat) $categories .= '<span> <b>' . $no++ . '.) </b>' . $cat->title . '</span><br>';
                    }

                    if ($res->status === 'reserved') $status = '<span class="badge badge-warning">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'used') $status = '<span class="badge badge-success">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'expired') $status = '<span class="badge badge-danger">' . strtoupper($res->status) . '</span>';
                    if ($res->status === 'cancelled') $status = '<span class="badge badge-light">' . strtoupper($res->status) . '</span>';

                    $details = '<span>' . $res->name . '</span><br>' .
                        '<span> <b>Organization</b>: ' . $res->organization . '</span><br>' .
                        '<span> <b>Phone</b>: ' . $res->phone . '</span><br>' .
                        '<span> <b>Email</b>: ' . $res->email . '</span><br>';

                    $show =  '<a href="' . route('biller.show-reserve-referral-promo-code', $res->uuid) . '" class="btn btn-secondary round mr-1">View</a>';
                    $edit = '<a href="' . route('biller.edit-reserve-referral-promo-code', $res->uuid) . '" class="btn btn-secondary round mr-1">Edit</a>';

                    $refererReservation = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($res->referer_uuid) ??
                        CustomersPromoCodeReservation::withoutGlobalScopes()->find($res->referer_uuid);

                    $referer = $refererReservation->name ?? optional(optional($refererReservation)->customer)->company;

                    return [

                        'customer' => $details,
                        'code' => $promoCode->code,
                        'products' => $products,
                        'categories' => $categories,
                        'created_at' => dateFormat($res->created_at),
                        'reserved_at' => (new DateTime($res->reserved_at))->format('d/m/Y, g:iA'),
                        'expires_at' => (new DateTime($res->expires_at))->format('d/m/Y, g:iA'),
                        'message' => $res->message,
                        'status' => $status,
                        'reserved_by' => '<i><b>Referral</b></i>',
                        'referred_by' => $referer,
                        'action' => $show . $edit,
                    ];
                });


            if (request('typeFilter') === 'customers') $tableData = collect($customerReservations)->sortBy('created_at')->values();
            else if (request('typeFilter') === 'third_parties') $tableData = collect($thirdPartyReservations)->sortBy('created_at')->values();
            else if (request('typeFilter') === 'referrals') $tableData = collect($referralReservations)->sortBy('created_at')->values();
            else $tableData = collect(array_merge($customerReservations->toArray(), $thirdPartyReservations->toArray(), $referralReservations->toArray()))->sortBy('created_at')->values();
            $tableData = $tableData->map(function ($item) {
                $item['created_at_sort'] = strtotime($item['created_at']);
                return $item;
            });

            return Datatables::of($tableData)
                ->editColumn('created_at', function ($tr) {
                    $date = $tr['created_at'];
                    return $date;
                })
                ->rawColumns(['action', 'products', 'categories', 'customer', 'status', 'referred_by', 'reserved_by'])
                ->make(true);
        }

        $promoCodes = PromotionalCode::orderBy('code')
            ->where(function ($query) {

                $query->whereHas('customersReservations')
                    ->orWhereHas('customersReservations');
            })
            ->get()
            ->map(function ($code) {

                return (object) [
                    'id' => $code->id,
                    'code' => $code->code,
                ];
            });

        $customers = Customer::orderBy('company')
            ->whereHas('promoCodeReservations')
            ->get()
            ->map(function ($c) {

                return (object) [
                    'id' => $c->id,
                    'code' => $c->company,
                ];
            });


        return view('focus.promotional_code_reservations.index', compact('promoCodes', 'customers'));
    }


    public function createCustomerReservation(Request $request)
    {
        if (!access()->allow('create-customer-reservation')) return response('', 403);


        $customers = Customer::orderBy('company')->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'company' => $customer->company,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ];
            });

        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();

        return view('focus.promotional_code_reservations.create', compact('customers', 'promoCodes'));
    }


    public function getViableTierResCount($codeId, $tier){

        $promoCode = PromotionalCode::withoutGlobalScopes()->findOrFail($codeId);

        $viableCustomerReservations = CustomersPromoCodeReservation::withoutGlobalScopes()
            ->where('promo_code_id', $promoCode->id)
            ->whereNotIn('status', ['expired', 'cancelled'])
            ->where('tier', $tier)
            ->get();

        $viable3pReservations = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()
            ->where('promo_code_id', $promoCode->id)
            ->whereNotIn('status', ['expired', 'cancelled'])
            ->where('tier', $tier)
            ->get();

        $viableReferralReservations = ReferralsPromoCodeReservation::withoutGlobalScopes()
            ->where('promo_code_id', $promoCode->id)
            ->whereNotIn('status', ['expired', 'cancelled'])
            ->where('tier', $tier)
            ->get();

        return count($viableCustomerReservations) + count($viable3pReservations) + count($viableReferralReservations);
    }

    /**
     * Reserve a promo code for an existing customer by an employee.
     */
    public function reserveForCustomer(Request $request)
    {
        if (!access()->allow('create-customer-reservation')) return response('', 403);

        $validated = $request->validate([
            'promo_code_id' => ['required', 'exists:promotional_codes,id'],
            'tier' => ['required', 'in:1,2,3',

                function (string $attribute, $value, Closure $fail) use ($request) {

                    $promoCode = PromotionalCode::findOrFail(request('promo_code_id'));

                    if ($value == 1 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_1) {

                        $fail("Action Denied! Reservation Limit for tier 1 reached.");
                    }
                    else if ($value == 2 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_2) {

                        $fail("Action Denied! Reservation Limit for tier 2 reached.");
                    }
                    else if ($value == 3 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_3) {

                        $fail("Action Denied! Reservation Limit for tier 3 reached.");
                    }
                }
            ],
            'customer_id' => ['required', 'exists:customers,id',

                function (string $attribute, $value, Closure $fail) use ($request) {

                    $existingReservation = CustomersPromoCodeReservation::where('promo_code_id', request('promo_code_id'))
                        ->where('customer_id', request('customer_id'))
                        ->where('status', 'reserved')
                        ->first();

                    if ($existingReservation) {
//                        $fail("Action Denied! There is an existing unused reservation for this customer on this code.");
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['string', 'max:20', 'nullable'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::beginTransaction();

            $promoCode = PromotionalCode::findOrFail($request->promo_code_id);

            $promoEnd = (new DateTime($promoCode->valid_until));
            $reservationEnd = (new DateTime())->add(new DateInterval('P' . $promoCode->reservation_period . 'D'));

            if ($promoEnd > $reservationEnd) $expiry = $reservationEnd;
            else $expiry = $promoEnd;


            $reservation = new CustomersPromoCodeReservation();
            $customer = Customer::find($validated['customer_id']);
            $reservation->uuid = Str::uuid()->toString();
            $customer_name = $customer->company ?: $customer->name;
            $validated['message'] = $this->personalizeMessage($validated['message'], [
                '[Client\'s Name]' => $customer_name,
            ]);
            $reservation->fill($validated);

            $reservation->fill([
                'reserved_by' => Auth::user()->id,
                'reserved_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'expires_at' => $expiry->format('Y-m-d H:i:s'),
                'redeemable_code' => $this->generateRedeemableCode(),
            ]);

            $reservation->save();

            // Increment reservations count
            $promoCode->increment('reservations_count');

            DB::commit();


            $company = Auth::user()->business;

            $this->sendSms(Auth::user()->ins, $validated['phone'], "". $validated['message'] . " Here's your Promotional Code Reservation: " . route('generate-promo-code-banner', $reservation->uuid) .". Courtesy of {$company->cname}");
            if($validated['email']){
                $this->sendEmail(
                    $validated['email'],
                    'A Promotional Code Reservation Has Been Created for You',
    
                    "
                        <p style='margin-bottom: 16px'>Dear {$customer->company},</p>
                        
                        <p style='margin-bottom: 20px'> {$reservation->message} </p>  
                        
                    " .
                    $this->generatePromoCodeBanner($reservation->uuid),
                    Auth::user()->ins
                );
            }


        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.reserve-promo-codes.index'), ['flash_success' => "Promo code reserved successfully for customer."]);
    }

    public function personalizeMessage($template, $replacements) {
        return strtr($template, $replacements);
    }

    public function sendPromotionMessage($template, $title, $discount, $company_contact)
    {
        // Replace placeholders in the template
        $message = strtr($template, [
            '{title}' => $title,
            '{discount}' => $discount,
            '{company_contact}' => $company_contact,
        ]);

        // You can return or use the message
        return $message;
    }



    public function showCustomerReservation($resId)
    {
        if (!access()->allow('manage-reserve-promo-codes')) return response('', 403);

        $isShowing = true;
        $isCustomer = true;

        $reservation = CustomersPromoCodeReservation::find($resId);
        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();

        $customers = Customer::orderBy('company')->where('id', $reservation->customer_id)->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'company' => $customer->company,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ];
            });

//        return compact('isShowing', 'isCustomer', 'reservation', 'promoCodes', 'customers');

        return view('focus.promotional_code_reservations.show', compact('reservation', 'isCustomer', 'promoCodes', 'isShowing', 'customers'));
    }

    public function editCustomerReservation($resId)
    {
        if (!access()->allow('edit-customer-reservation')) return response('', 403);

        $isCustomer = true;

        $reservation = CustomersPromoCodeReservation::find($resId);
        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();

        $customers = Customer::orderBy('company')->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'company' => $customer->company,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                ];
            });


        return view('focus.promotional_code_reservations.edit', compact('reservation', 'isCustomer', 'promoCodes', 'customers'));
    }

    public function updateCustomerReservation(Request $request, $resId)
    {
        if (!access()->allow('edit-customer-reservation')) return response('', 403);

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'whatsapp_number' => ['string', 'max:20', 'nullable'],
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
            'status' => ['required', 'in:reserved,cancelled'],
            'tier' => ['required', 'in:1,2,3',

                function (string $attribute, $value, Closure $fail) use ($resId) {

                    $reservation = CustomersPromoCodeReservation::find($resId);
                    $promoCode = $reservation->promoCode;

                    if ($reservation['status'] != request('status')) {

                        if ($value == 1 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_1) {

                            $fail("Action Denied! Reservation Limit for tier 1 reached.");
                        } else if ($value == 2 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_2) {

                            $fail("Action Denied! Reservation Limit for tier 2 reached.");
                        } else if ($value == 3 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_3) {

                            $fail("Action Denied! Reservation Limit for tier 3 reached.");
                        }
                    }
                }
            ],
        ]);

        try {
            DB::beginTransaction();

            $reservation = CustomersPromoCodeReservation::find($resId);

            // Check if the `status` has changed
            $originalStatus = $reservation->status;
            $newStatus = $validated['status'];

            if ($originalStatus !== $newStatus) {
                // Handle the status change
                if ($newStatus === 'cancelled') {

                    // Code to handle transition to cancelled
                    $reservation->promoCode()->update([
                        'reservations_count' => DB::raw('reservations_count - 1')
                    ]);
                }
                elseif ($newStatus === 'reserved') {


                    $viableCustomerReservations = $reservation->promoCode->customersReservations()
                        ->whereNotIn('status', ['expired', 'cancelled'])
                        ->get();

                    $viable3pReservations = $reservation->promoCode->thirdPartiesReservations()
                        ->whereNotIn('status', ['expired', 'cancelled'])
                        ->get();

                    $viableResCount = count($viableCustomerReservations) + count($viable3pReservations) + 1;

                    if ($viableResCount >= $reservation->promoCode->usage_limit){

                        return redirect()->back()->with('flash_error', 'Promo code reservation limit reached.');
                    }

                    // Code to handle transition to reserved
                    $reservation->promoCode()->update([
                        'reservations_count' => DB::raw('reservations_count + 1')
                    ]);
                }
            }

            $reservation->fill($validated);
            $reservation->save();

            DB::commit();


            $customer = Customer::find($reservation->customer_id);

            $company = Auth::user()->business;

            $this->sendSms(auth()->user()->ins, $validated['phone'], "". $validated['message'] . " Here's your Promotional Code Reservation: " . route('generate-promo-code-banner', $reservation->uuid) .". Courtesy of {$company->cname}");
            $this->sendEmail(
                $validated['email'],
                'A Promotional Code Reservation Has Been Created for You',

                "
                    <p style='margin-bottom: 16px'>Dear {$customer->company},</p>
                    
                    <p style='margin-bottom: 20px'> {$reservation->message} </p>  

                " .
                $this->generatePromoCodeBanner($reservation->uuid),
                Auth::user()->ins
            );


        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.show-reserve-customer-promo-code', $resId), ['flash_success' => "Promo code reservation updated successfully."]);
    }


    public function createThirdPartyReservation(Request $request)
    {
        if (!access()->allow('create-3p-reservation')) return response('', 403);

        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();


        return view('focus.promotional_code_reservations.create', compact('promoCodes'));
    }



    /**
     * Reserve a promo code for a third party via API link.
     */
    public function reserveForThirdParty(Request $request)
    {
        if (!access()->allow('create-3p-reservation')) return response('', 403);

        $validated = $request->validate([
            'promo_code_id' => ['required', 'exists:promotional_codes,id'],
            'tier' => ['required', 'in:1,2,3',

                function (string $attribute, $value, Closure $fail) use ($request) {

                    $promoCode = PromotionalCode::findOrFail(request('promo_code_id'));

                    if ($value == 1 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_1) {

                        $fail("Action Denied! Reservation Limit for tier 1 reached.");
                    }
                    else if ($value == 2 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_2) {

                        $fail("Action Denied! Reservation Limit for tier 2 reached.");
                    }
                    else if ($value == 3 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_3) {

                        $fail("Action Denied! Reservation Limit for tier 3 reached.");
                    }
                }
            ],
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['string', 'max:20', 'nullable'],
            'email' => ['nullable', 'email', 'max:255',

//                function (string $attribute, $value, Closure $fail) use ($request) {
//
//                    $existingReservation = ThirdPartiesPromoCodeReservation::where('promo_code_id', request('promo_code_id'))
//                        ->where('email', request('email'))
//                        ->where('status', 'reserved')
//                        ->first();
//
//                    if ($existingReservation) {
//                        $fail("Action Denied! There is an existing unused reservation for this entity's email on this code.");
//                    }
//                },
            ],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            DB::beginTransaction();


            $promoCode = PromotionalCode::findOrFail($request->promo_code_id);

            $promoEnd = (new DateTime($promoCode->valid_until));
            $reservationEnd = (new DateTime())->add(new DateInterval('P' . $promoCode->reservation_period . 'D'));

            if ($promoEnd > $reservationEnd) $expiry = $reservationEnd;
            else $expiry = $promoEnd;
            $company = Auth::user()->business;
            $discountValue = $promoCode->discount_value;
            $discount = $promoCode->discount_type == 'fixed' ? "KES {$discountValue}" : "{$discountValue} %";
            $validated['message'] = $this->sendPromotionMessage($validated['message'],$promoCode->description, $discount,$company->phone);

            $reservation = new ThirdPartiesPromoCodeReservation();
            $reservation->uuid = Str::uuid()->toString();
            $reservation->fill($validated);

            $reservation->fill([
                'reserved_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'expires_at' => $expiry->format('Y-m-d H:i:s'),
                'redeemable_code' => $this->generateRedeemableCode(),
            ]);

            $reservation->save();

            // Increment reservations count
            $promoCode->increment('reservations_count');

            DB::commit();   

            $this->sendSms($promoCode->company_id, $validated['phone'], "From: {$company->cname} | " . $validated['message'] . ". Here is your redeemable promo code '{$reservation->redeemable_code}' - click and claim it here: " . route('generate-promo-code-banner', $reservation->uuid) ." ");
            $this->sendEmail(
                $validated['email'],
                'A Promotional Code Reservation Has Been Created for You',

                "
                    <p style='margin-bottom: 16px'>Dear {$validated['name']},</p>                  
                 
                    <p style='margin-bottom: 20px'> {$reservation->message} </p>  
                " .
                $this->generatePromoCodeBanner($reservation->uuid),
                Auth::user()->ins
            );


        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.reserve-promo-codes.index'), ['flash_success' => "Promo code reserved successfully for third party."]);
    }



    public function showThirdPartyReservation($resId)
    {
        if (!access()->allow('manage-reserve-promo-codes')) return response('', 403);

        $isShowing = true;
        $isCustomer = true;

        $reservation = ThirdPartiesPromoCodeReservation::find($resId);
        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();

        $linkCustomers = Customer::orderBy('company')->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'company' => $customer->company,
                ];
            });

//        return compact('isShowing', 'isCustomer', 'reservation', 'promoCodes', 'customers');

        return view('focus.promotional_code_reservations.show', compact('reservation', 'isCustomer', 'promoCodes', 'isShowing', 'linkCustomers'));
    }


    public function editThirdPartyReservation($resId)
    {
        if (!access()->allow('edit-3p-reservation')) return response('', 403);

        $isCustomer = false;

        $reservation = ThirdPartiesPromoCodeReservation::find($resId);
        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();

        $linkCustomers = Customer::orderBy('company')->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'company' => $customer->company,
                ];
            });


        return view('focus.promotional_code_reservations.edit', compact('reservation', 'isCustomer', 'promoCodes', 'linkCustomers'));
    }


    public function updateThirdPartyReservation(Request $request, $resId)
    {
        if (!access()->allow('edit-3p-reservation')) return response('', 403);

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'whatsapp_number' => ['string', 'max:20', 'nullable'],
            'email' => 'nullable|email|max:255',
            'customer_id' => ['nullable', 'exists:customers,id'],
            'message' => 'required|nullable|string|max:2000',
            'status' => ['required', 'in:reserved,cancelled'],
            'tier' => ['required', 'in:1,2,3',

                function (string $attribute, $value, Closure $fail) use ($resId) {

                    $reservation = ThirdPartiesPromoCodeReservation::find($resId);
                    $promoCode = $reservation->promoCode;

                    if ($reservation['status'] != request('status')) {

                        if ($value == 1 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_1) {

                            $fail("Action Denied! Reservation Limit for tier 1 reached.");
                        } else if ($value == 2 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_2) {

                            $fail("Action Denied! Reservation Limit for tier 2 reached.");
                        } else if ($value == 3 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_3) {

                            $fail("Action Denied! Reservation Limit for tier 3 reached.");
                        }
                    }
                }
            ],
        ]);

        try {
            DB::beginTransaction();

            $reservation = ThirdPartiesPromoCodeReservation::find($resId);

            // Check if the `status` has changed
            $originalStatus = $reservation->status;
            $newStatus = $validated['status'];

            if ($originalStatus !== $newStatus) {
                // Handle the status change
                if ($newStatus === 'cancelled') {

                    // Code to handle transition to cancelled
                    $reservation->promoCode()->update([
                        'reservations_count' => DB::raw('reservations_count - 1')
                    ]);
                }
                elseif ($newStatus === 'reserved') {

                    // Code to handle transition to reserved

                    $viableCustomerReservations = $reservation->promoCode->customersReservations()
                        ->whereNotIn('status', ['expired', 'cancelled'])
                        ->get();

                    $viable3pReservations = $reservation->promoCode->thirdPartiesReservations()
                        ->whereNotIn('status', ['expired', 'cancelled'])
                        ->get();

                    $viableResCount = count($viableCustomerReservations) + count($viable3pReservations) + 1;

                    if ($viableResCount >= $reservation->promoCode->usage_limit){

                        return redirect()->back()->with('flash_error', 'Promo code reservation limit reached.');
                    }

                    $reservation->promoCode()->update([
                        'reservations_count' => DB::raw('reservations_count + 1')
                    ]);
                }
            }

            $reservation->fill($validated);
            $reservation->save();

            DB::commit();


            $company = Auth::user()->business;

            $this->sendSms(auth()->user()->ins, $validated['phone'], "From: {$company->cname} | " . $validated['message'] . ". Here is your redeemable promo code '{$reservation->redeemable_code}' - click and claim it here: " . route('generate-promo-code-banner', $reservation->uuid) ."");
            $this->sendEmail(
                $validated['email'],
                'A Promotional Code Reservation Has Been Created for You',

                "
                    <p style='margin-bottom: 16px'>Dear {$reservation->name},</p>                  
                 
                    <p style='margin-bottom: 20px'> {$reservation->message} </p>  
                " .
                $this->generatePromoCodeBanner($reservation->uuid),
                Auth::user()->ins
            );


        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.show-reserve-3p-promo-code', $resId), ['flash_success' => "Promo code reservation updated successfully."]);

    }


    public function generatePromoCodeBanner($reservationUuid, $forInternal = false){


        $customerReservation = CustomersPromoCodeReservation::withoutGlobalScopes()->where('uuid', $reservationUuid)->get();

        $isCustomerReservation = false;

        if (empty($customerReservation->first())) {

            $thirdParty = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->where('uuid', $reservationUuid)->get();
            $referral = ReferralsPromoCodeReservation::withoutGlobalScopes()->where('uuid', $reservationUuid)->get();

            $thirdPartyReservation = $thirdParty->first() ? $thirdParty : $referral;
        }

        if (empty($customerReservation->first()) && empty($thirdPartyReservation->first())) return false;

        $payload = null;


        if ($customerReservation->first()) {

            $isCustomerReservation = true;

            $payload = $customerReservation
                ->map(function ($res) {

                    $productIds = PromotionalCodeProduct::where('promotional_code_id', $res->promo_code_id)->get()->pluck('product_variation_id');
                    $productVariations = ProductVariation::withoutGlobalScopes()->whereIn('id', $productIds)->get();

                    $products = '';
                    if (count($productVariations) > 0) {

                        $no = 1;
                        foreach ($productVariations as $product) $products .= "<span>" . $no++ . ".) {$product->name} </span><br>";
                    }

                    $categoryIds = PromotionalCodeProductCategory::where('promotional_code_id', $res->promo_code_id)->get()->pluck('product_category_id');
                    $productCategories = Productcategory::withoutGlobalScopes()->whereIn('id', $categoryIds)->get();


                    $categories = '';
                    if (count($productCategories) > 0) {

                        $no = 1;
                        foreach ($productCategories as $cat) $categories .= '<span> <b>' . $no++ . '.) </b>' . $cat->title . '</span><br>';
                    }

                    $customer = Customer::withoutGlobalScopes()->find($res->customer_id);

                    $details = '<p style="margin: 20px 0; font-size: 14px; color: #555;">' .
                        '<span><b>For</b>: <span>' . ($customer ? optional($customer)->company : $res->name) . '</span></span><br>' .
                        '<span> <b>Phone</b>: ' . $res->phone . '</span><br>' .
                        '<span> <b>Email</b>: ' . $res->email . '</span><br>' .
                        '</p>';

                    $cashbackDetails = null;
                    $cash_back = null;

                    if (optional($res->promoCode)->cash_back_2 || optional($res->promoCode)->cash_back_3) {

                        $currency = Currency::find((optional($res->promoCode))->currency_id);
                        $cashBackColumn = 'cash_back_'.$res->tier;
                        $currency_code = $currency->code ?? "KSH";
                        $cash_back = $this->formatCommission($res->promoCode, $cashBackColumn);
                        // $cash_back = (optional($res->promoCode))->commision_type == 'fixed' ? $currency_code.' '.(optional($res->promoCode)->$cashBackColumn) : (optional($res->promoCode)->$cashBackColumn).'%';
                        $cashbackDetails = '<span> Refer a friend and earn a reward of ' . $cash_back . ' </span><br>';
                    }


                    $promoCode = PromotionalCode::withoutGlobalScopes()->find($res->promo_code_id);
                    $company = Company::withoutGlobalScopes()->find((optional($promoCode)->company_id));
                    $reserver = User::withoutGlobalScopes()->find($res->reserved_by);
                    $prefix = (new CompanyPromotionalPrefixController())->getCompanyCode($company->id);


                    $validFrom = (new DateTime($promoCode->valid_from))->format('jS M Y, g:iA');
                    $validUntil = (new DateTime($promoCode->valid_until))->format('jS M Y, g:iA');
                    $period = "<span>📅 Promo offer valid From: {$validFrom} <br> 📅 To: {$validUntil} </span>";

                    $unit = '';
                    $discountUnit = $promoCode->discount_type === 'fixed' ? '' : '%';
                    if(!$discountUnit) $unit = 'KES ';

                    if($res->tier == 1) $discountValue = $promoCode->discount_value;
                    else if ($res->tier == 2) $discountValue = $promoCode->discount_value_1;
                    else $discountValue = $promoCode->discount_value_2;

                    $discount = "<span style='font-size: 14px'>Get a discount of <span style='font-size: 16px'><b>{$unit}{$discountValue}{$discountUnit}</b></span> on:</span>";

                    $url = route('reserve-referral-promo-code', $res->uuid);
                    $contactUrl = route('contact-us', ['reservationUuid' => $res->uuid]) . '?companyId=' . $company->id;


                    $referrer = CustomersPromoCodeReservation::find($res->referer_uuid) ??
                        ThirdPartiesPromoCodeReservation::find($res->referer_uuid) ??
                        ReferralsPromoCodeReservation::find($res->referer_uuid);
                    $referrerName = null;

                    if ($referrer) {

                        if ($referrer->customer_id) {

                            $cust = Customer::find($referrer->customer_id);

                            $referrerName = 'Customer Referral | ' . $cust->company . ' || ' . $cust->phone . ' | ' . $cust->email;
                        }
                        else $referrerName = $referrer->name . ' | ' . $referrer->organization . ' || ' . $referrer->phone . ' | ' . $referrer->email;
                    }

                    return (object) [

                        'uuid' => $res->uuid,
                        'redeemable_code' => $res->redeemable_code,
                        'url' => $url,
                        'contact_url' => $contactUrl,
                        'customer' => $details,
                        'customer_company' => optional($customer)->company,
                        'res' => $res,
                        'customer_phone' => $res->phone,
                        'customer_email' => $res->email,
                        'promo_code' => $promoCode,
                         'tier' => $res->tier,
                        'period' => $period,
                        'discount_value' => $unit.$discountValue.$discountUnit,
                        'discount_figure' => $discountValue,
                        'cash_back' => $cash_back,
                        'discount' => $discount,
                        'products' => $products,
                        'categories' => $categories,
                        'reserved_at' => (new DateTime($res->reserved_at))->format('d/m/Y, g:iA'),
                        'expires_at' => (new DateTime($res->expires_at))->format('d/m/Y, g:iA'),
                        'message' => $res->message,
                        'reserved_by' => optional($reserver)->first_name . ' ' . optional($reserver)->last_name,
                        'referer' => $referrer,
                        'company' => $company,
                        'cb_details' => $cashbackDetails,
                        'tier_discs' => ['ti' => $promoCode->discount_value, 't2' => $promoCode->discount_value_2, 't3' => $promoCode->discount_value_3]
                    ];
                })
                ->first();
        }

        else {

            $payload = $thirdPartyReservation->map(function ($res) {


                $productIds = PromotionalCodeProduct::where('promotional_code_id', $res->promo_code_id)->get()->pluck('product_variation_id');
                $productVariations = ProductVariation::withoutGlobalScopes()->whereIn('id', $productIds)->get();

                $products = '';
                if (count($productVariations) > 0) {

                    $no = 1;
                    foreach ($productVariations as $product) $products .= "<span>" . $no++ . ".) {$product->name} </span><br>";
                }

                $categoryIds = PromotionalCodeProductCategory::where('promotional_code_id', $res->promo_code_id)->get()->pluck('product_category_id');
                $productCategories = Productcategory::withoutGlobalScopes()->whereIn('id', $categoryIds)->get();


                $categories = '';
                if (count($productCategories) > 0) {

                    $no = 1;
                    foreach ($productCategories as $cat) $categories .= '<span> <b>' . $no++ . '.) </b>' . $cat->title . '</span><br>';
                }

                $details = '<p style="margin: 20px 0; font-size: 14px; color: #555;">' .
                    '<span><b>For</b>: <span>' . $res->name . '</span></span><br>' .
                    '<span> <b>Organization</b>: ' . $res->organization . '</span><br>' .
                    '<span> <b>Phone</b>: ' . $res->phone . '</span><br>' .
                    '<span> <b>Email</b>: ' . $res->email . '</span><br>' .
                    '</p>';


                $cashbackDetails = null;
                $cash_back = null;

                if (optional($res->promoCode)->cash_back_2 || optional($res->promoCode)->cash_back_3) {

                    $cashBackColumn = 'cash_back_'.$res->tier;
                    $currency = Currency::find((optional($res->promoCode))->currency_id);
                    $currency_code = $currency->code ?? "KSH";
                    $cash_back = $this->formatCommission($res->promoCode, $cashBackColumn);
                    // $cash_back = (optional($res->promoCode))->commision_type == 'fixed' ? $currency_code.' '.(optional($res->promoCode)->$cashBackColumn) : (optional($res->promoCode)->$cashBackColumn).'%';
                    $cashbackDetails = '<span> Refer a friend and earn a reward of ' . $cash_back . ' </span><br>';
                }


                $promoCode = PromotionalCode::withoutGlobalScopes()->find($res->promo_code_id);
                $company = Company::withoutGlobalScopes()->find((optional($promoCode)->company_id));
                $reserver = User::withoutGlobalScopes()->find($res->reserved_by);
                $prefix = (new CompanyPromotionalPrefixController())->getCompanyCode($company->id);

                $validFrom = (new DateTime($promoCode->valid_from))->format('jS M Y, g:iA');
                $validUntil = (new DateTime($promoCode->valid_until))->format('jS M Y, g:iA');
                $period = "<span>📅 Promo offer valid From: {$validFrom} <br> 📅 To: {$validUntil} </span>";

                $unit = '';
                $discountUnit = $promoCode->discount_type === 'fixed' ? '' : '%';
                if(!$discountUnit) $unit = 'KES ';

                if ($res->tier == 1) $discountValue = $promoCode->discount_value;
                else if ($res->tier == 2) $discountValue = $promoCode->discount_value_2;
                else $discountValue = $promoCode->discount_value_3;

                $discount = "<span style='font-size: 14px'>Get a discount of <span style='font-size: 16px'><b>{$unit}{$discountValue}{$discountUnit}</b></span> on:</span>";

                $url = route('reserve-referral-promo-code', $res->uuid);
                $contactUrl = route('contact-us', ['reservationUuid' => $res->uuid]) . '?companyId=' . $company->id;

                $referrer = CustomersPromoCodeReservation::find($res->referer_uuid) ??
                    ThirdPartiesPromoCodeReservation::find($res->referer_uuid) ??
                    ReferralsPromoCodeReservation::find($res->referer_uuid);
                $referrerName = null;

                if ($referrer) {

                    if ($referrer->customer_id) {

                        $cust = Customer::find($referrer->customer_id);

                        $referrerName = 'Customer Referral | ' . $cust->company . ' || ' . $cust->phone . ' | ' . $cust->email;
                    }
                    else $referrerName = $referrer->name . ' | ' . $referrer->organization . ' || ' . $referrer->phone . ' | ' . $referrer->email;
                }

                return (object) [

                    'uuid' => $res->uuid,
                    'redeemable_code' => $res->redeemable_code,
                    'url' => $url,
                    'contact_url' => $contactUrl,
                    'customer' => $details,
                    'customer_company' => null,
                    'res' => $res,
                    'promo_code' => $promoCode,
                    'tier' => $res->tier,
                    'period' => $period,
                    'discount_value' => $unit.$discountValue.$discountUnit,
                    'discount_figure' => $discountValue,
                    'discount' => $discount,
                    'cash_back' => $cash_back,
                    'products' => $products,
                    'categories' => $categories,
                    'reserved_at' => (new DateTime($res->reserved_at))->format('d/m/Y, g:iA'),
                    'expires_at' => (new DateTime($res->expires_at))->format('d/m/Y, g:iA'),
                    'message' => $res->message,
                    'reserved_by' => optional($reserver)->first_name . ' ' . optional($reserver)->last_name,
                    'referer' => $referrerName,
                    'company' => $company,
                    'cb_details' => $cashbackDetails,
                    'tier_discs' => ['ti' => $promoCode->discount_value, 't2' => $promoCode->discount_value_2, 't3' => $promoCode->discount_value_3]
                ];
            })
            ->first();

        }

        return view('focus.promotional_code_reservations.banner', compact('payload', 'forInternal', 'isCustomerReservation'))->render();
    }

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
            $percentV = ($percentValue / 100) * $promoCode->total_commission;
            return $percentV.'%';
        }

        if ($promoCode->total_commission_type === 'fixed') {
            $fieldName = $field . '_amount';
            return 'Ksh '.$promoCode->$fieldName;
        }

        return 0;
    }

    public function promoCodeLink($companyPrefix, $promoCode, $reservationUuid)
    {


        $customerReservation = CustomersPromoCodeReservation::withoutGlobalScopes()->where('uuid', $reservationUuid)->get();
        if (empty($customerReservation)) $thirdPartyReservation = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->where('uuid', $reservationUuid)->get();

        return $customerReservation ?? $thirdPartyReservation;

        if (empty($customerReservation) && empty($thirdPartyReservation)) return false;

    }


    public function getCustomerReservations($customerId) {

        $reservations = CustomersPromoCodeReservation::where('customer_id', $customerId)
            ->where('status', 'reserved')
//            ->whereDoesntHave('emailedReservation')
            ->get()
            ->map(function ($res) {

                return (object) [

                    'uuid' => $res->uuid,
                    'code' => $res->promoCode->code,
                    'banner' => $this->generatePromoCodeBanner($res->uuid)
                ];
            });

        return $reservations;
    }


    public function getThirdPartiesReservations(Request $request) {

        $reservations = ThirdPartiesPromoCodeReservation::where('email', request('email'))
            ->where('status', 'reserved')
            ->whereDoesntHave('emailedReservation')
            ->when(request('search'), function($q) {

                $q->whereHas('promoCode', function($query) {
                    $query->where('name', 'like', '%' . request('search') . '%');
                });
            })
            ->get()
            ->map(function ($res) {

                return [

                    'uuid' => $res->uuid,
                    'code' => $res->promoCode->code,
                    'banner' => $this->generatePromoCodeBanner($res->uuid)
                ];
            });

        return response()->json($reservations);
    }


    /**
     * @throws \DateMalformedStringException
     */
    public function createReferralReservation(Request $request, $resUuid)
    {

        $referer = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($resUuid) ??
            CustomersPromoCodeReservation::withoutGlobalScopes()->find($resUuid) ??
            ReferralsPromoCodeReservation::withoutGlobalScopes()->find($resUuid);

        $refererName = null;

        if ($referer) {

            if ($referer->customer_id) {

                $cust = Customer::withoutGlobalScopes()->find($referer->customer_id);

                $refererName = 'Customer Referral | ' . $cust->company . ' || ' . $cust->phone . ' | ' . $cust->email;
            }
            else $refererName = $referer->name . ' | ' . $referer->organization . ' || ' . $referer->phone . ' | ' . $referer->email;
        }

        $refererChain = [];
        $parentReferer = $referer;

        while ($parentReferer){

            $customerReferer = CustomersPromoCodeReservation::withoutGlobalScopes()->find($parentReferer->referer_uuid);

            $otherReferer = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($parentReferer->referer_uuid) ??
                ReferralsPromoCodeReservation::withoutGlobalScopes()->find($parentReferer->referer_uuid);

            $parentRefererName = null;

            if ($customerReferer) {

                $customer = Customer::withoutGlobalScopes()->find($customerReferer->customer_id);
                $parentRefererName = 'Customer Referral | ' . $customer->company . ' || ' . $customer->phone . ' | ' . $customer->email;

                array_push($refererChain, $parentRefererName);
                $parentReferer = $customerReferer;
            }
            else if ($otherReferer){

                $parentRefererName = $otherReferer->name . ' | ' . $otherReferer->organization . ' || ' . $otherReferer->phone . ' | ' . $otherReferer->email;

                array_push($refererChain, $parentRefererName);
                $parentReferer = $otherReferer;
            }
            else $parentReferer = null;
        }


        $code = PromotionalCode::withoutGlobalScopes()->find($referer->promo_code_id);

        if ($code->status == 0) return view('focus.promotional_code_reservations.promo-cancelled');

        $today = new DateTime();
        $expiry = new DateTime($code->valid_until);

        if ($today > $expiry) return view('focus.promotional_code_reservations.referral-expired');

        $tier1Open = $this->getViableTierResCount($code->id, 1) < $code->res_limit_1;
        $tier2Open = $this->getViableTierResCount($code->id, 2) < $code->res_limit_2;
        $tier3Open = $this->getViableTierResCount($code->id, 3) < $code->res_limit_3;

        if (!$tier1Open && !$tier2Open && !$tier3Open) return view('focus.promotional_code_reservations.referral-unavailable');


        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();


        return view('focus.promotional_code_reservations.create', compact('promoCodes', 'referer', 'resUuid', 'tier1Open', 'tier2Open', 'tier3Open', 'refererName', 'refererChain'));
    }


    /**
     * Reserve a promo code for a third party via API link.
     */
    public function reserveForReferral(Request $request)
    {

        $validated = $request->validate([
            'promo_code_id' => ['required', 'exists:promotional_codes,id'],
            'tier' => ['required', 'in:1,2,3'],
            'referer_uuid' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
//            'organization' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['required', 'string', 'max:20', 'nullable'],
            'email' => ['nullable', 'email', 'max:255',

                function (string $attribute, $value, Closure $fail) use ($request) {

                    $existing3pReservation = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->where('promo_code_id', request('promo_code_id'))
                        ->where('email', request('email'))
                        ->where('status', 'reserved')
                        ->first();

                    $existingReferralReservation = ReferralsPromoCodeReservation::withoutGlobalScopes()->where('promo_code_id', request('promo_code_id'))
                        ->where('email', request('email'))
                        ->where('status', 'reserved')
                        ->first();

//                    if ($existing3pReservation || $existingReferralReservation) {
//                        $fail("Action Denied! There is an existing unused reservation for this entity's email on this code.");
//                    }
                },
            ],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            DB::beginTransaction();


            $promoCode = PromotionalCode::findOrFail($request->promo_code_id);

            $promoEnd = (new DateTime($promoCode->valid_until));
            $reservationEnd = (new DateTime())->add(new DateInterval('P' . $promoCode->reservation_period . 'D'));

            if ($promoEnd > $reservationEnd) $expiry = $reservationEnd;
            else $expiry = $promoEnd;

            $reservation = new ReferralsPromoCodeReservation();
            $reservation->uuid = Str::uuid()->toString();
            $reservation->referer_uuid = $validated['referer_uuid'];
            $reservation->organization = $validated['name'];
            $reservation->fill($validated);

            $reservation->fill([
                'reserved_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'expires_at' => $expiry->format('Y-m-d H:i:s'),
                'redeemable_code' => $this->generateRedeemableCode(),
            ]);

            $reservation->save();
            $referral = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid) ??
                    CustomersPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid) ??
                    ReferralsPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid);

            // Increment reservations count
            $promoCode->increment('reservations_count');

            DB::commit();
            $tenant = Company::find($promoCode->company_id);
            $discountValue = $promoCode->discount_value;
            $discount = $promoCode->discount_type == 'fixed' ? "KES ".numberFormat($discountValue)."" : "{$discountValue} %";
            $msg = $this->buildReferralMessage($reservation, [
                'tenant_name'       => $tenant->cname,
                'tenant_promo_code' => $promoCode->code,
                'discount_text'     => $discount,
            ]);

            $this->sendSms($promoCode->company_id, $validated['phone'], "From: {$referral->name} | ".$validated['message'] . ". Here is your redeemable promo code '{$reservation->redeemable_code}' - click and claim it here: " . route('generate-promo-code-banner', $reservation->uuid));

            SendBulkSms::dispatch($promoCode->company_id, $tenant->notification_number, $msg);
            $this->sendEmail(
                $validated['email'],
                'A Promotional Code Reservation Has Been Created for You',

                "
                    <p style='margin-bottom: 16px'>Dear {$validated['name']},</p>                  
                 
                    <p style='margin-bottom: 20px'> {$reservation->message} </p>  
                " .
                $this->generatePromoCodeBanner($reservation->uuid),
                $promoCode->company_id
            );

        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return view('focus.promotional_code_reservations.referral-success');
    }

    public function showReferralReservation($resUuid)
    {

        $isShowing = true;
        $isCustomer = false;

        $reservation = ReferralsPromoCodeReservation::find($resUuid);

        $promoCodes = PromotionalCode::where('id', $reservation->promo_code_id)
            ->get()
            ->map(function ($promotion) {

                $validFrom = (new DateTime($promotion->valid_from))->format('jS M Y, g:iA');
                $validUntil = (new DateTime($promotion->valid_until))->format('jS M Y, g:iA');
                $period = $validFrom . ' to ' . $validUntil;

                $products = $promotion->productVariations;
                $categories = $promotion->productCategories;

                $items = [];

                if (count($products) > 0) {
                    $items = $promotion->productVariations->map(fn($product) => $product->code . ' | ' . $product->name)->toArray(); // Convert to array
                } elseif (count($categories) > 0) {
                    $items = $promotion->productCategories->map(fn($category) => $category->title)->toArray(); // Convert to array
                }

                return (object) [
                    'id' => $promotion->id,
                    'code' => $promotion->code,
                    'type' => ucfirst(str_replace('_', ' ', $promotion->promo_type)),
                    'period' => $period,
                    'description' => $promotion->description,
                    'items' => $items, // Explicitly an array
                    'discount_type' => ucfirst($promotion->discount_type),
                    'discount_value' => number_format($promotion->discount_value, 2),
                    'discount_value_2' => number_format($promotion->discount_value_2, 2),
                    'discount_value_3' => number_format($promotion->discount_value_3, 2),
                    'commision_type' => ucfirst($promotion->commision_type),
                    'cash_back_1' => number_format($promotion->cash_back_1, 2),
                    'cash_back_2' => number_format($promotion->cash_back_2, 2),
                    'cash_back_3' => number_format($promotion->cash_back_3, 2),
                    'description_promo' => $promotion->description_promo,
                ];
            });

        $linkCustomers = Customer::orderBy('company')->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'company' => $customer->company,
                ];
            });


        $referer = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid) ??
            CustomersPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid) ??
            ReferralsPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid);

        $refererName = null;

        if ($referer) {

            if ($referer->customer_id) {

                $cust = Customer::withoutGlobalScopes()->find($referer->customer_id);

                $refererName = 'Customer Referral | ' . $cust->company . ' || ' . $cust->phone . ' | ' . $cust->email;
            }
            else $refererName = $referer->name . ' | ' . $referer->organization . ' || ' . $referer->phone . ' | ' . $referer->email;
        }

        $refererChain = [];
        $parentReferer = $referer;

        while ($parentReferer){

            $customerReferer = CustomersPromoCodeReservation::withoutGlobalScopes()->find($parentReferer->referer_uuid);

            $otherReferer = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($parentReferer->referer_uuid) ??
                ReferralsPromoCodeReservation::withoutGlobalScopes()->find($parentReferer->referer_uuid);

            $parentRefererName = null;

            if ($customerReferer) {

                $customer = Customer::withoutGlobalScopes()->find($customerReferer->customer_id);
                $parentRefererName = 'Customer Referral | ' . $customer->company . ' || ' . $customer->phone . ' | ' . $customer->email;

                array_push($refererChain, $parentRefererName);
                $parentReferer = $customerReferer;
            }
            else if ($otherReferer){

                $parentRefererName = $otherReferer->name . ' | ' . $otherReferer->organization . ' || ' . $otherReferer->phone . ' | ' . $otherReferer->email;

                array_push($refererChain, $parentRefererName);
                $parentReferer = $otherReferer;
            }
            else $parentReferer = null;
        }

        $cashBack = null;

        if ($referer->tier == 1) $cashBack = number_format($referer->promoCode->cash_back_1, 2);
        else if ($referer->tier == 2) $cashBack = number_format($referer->promoCode->cash_back_2, 2);

//        return compact('referer', 'cashBack');

        return view('focus.promotional_code_reservations.show', compact('reservation', 'isCustomer', 'promoCodes', 'isShowing', 'linkCustomers', 'refererName', 'refererChain', 'cashBack'));
    }


    public function editReferralReservation($resId)
    {

        if (!access()->allow('edit-referral-reservation')) return response('', 403);

        $isCustomer = false;
        $isReferral = true;

        $reservation = ReferralsPromoCodeReservation::find($resId);
        $promoCodes = (new PromotionalCodeController())->getActiveReservableCodesMetadata();

        $linkCustomers = Customer::orderBy('company')->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'company' => $customer->company,
                ];
            });


        return view('focus.promotional_code_reservations.edit', compact('reservation', 'isCustomer', 'isReferral', 'promoCodes', 'linkCustomers'));
    }

    public function updateReferralReservation(Request $request, $resId)
    {
        if (!access()->allow('edit-referral-reservation')) return response('', 403);

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'whatsapp_number' => ['string', 'max:20', 'nullable'],
            'email' => 'nullable|email|max:255',
            'customer_id' => ['nullable', 'exists:customers,id'],
            'message' => 'required|nullable|string|max:2000',
            'status' => ['required', 'in:reserved,cancelled'],
            'tier' => ['required', 'in:1,2,3',

                function (string $attribute, $value, Closure $fail) use ($resId) {

                    $reservation = ReferralsPromoCodeReservation::find($resId);
                    $promoCode = $reservation->promoCode;

                    if ($reservation['status'] != request('status')) {

                        if ($value == 1 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_1) {

                            $fail("Action Denied! Reservation Limit for tier 1 reached.");
                        } else if ($value == 2 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_2) {

                            $fail("Action Denied! Reservation Limit for tier 2 reached.");
                        } else if ($value == 3 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_3) {

                            $fail("Action Denied! Reservation Limit for tier 3 reached.");
                        }
                    }
                }
            ],
        ]);

        try {
            DB::beginTransaction();

            $reservation = ReferralsPromoCodeReservation::find($resId);

            // Check if the `status` has changed
            $originalStatus = $reservation->status;
            $newStatus = $validated['status'];

            if ($originalStatus !== $newStatus) {
                // Handle the status change
                if ($newStatus === 'cancelled') {

                    // Code to handle transition to cancelled
                    $reservation->promoCode()->update([
                        'reservations_count' => DB::raw('reservations_count - 1')
                    ]);
                }
                elseif ($newStatus === 'reserved') {

                    // Code to handle transition to reserved

                    $viableCustomerReservations = $reservation->promoCode->customersReservations()
                        ->whereNotIn('status', ['expired', 'cancelled'])
                        ->get();

                    $viable3pReservations = $reservation->promoCode->thirdPartiesReservations()
                        ->whereNotIn('status', ['expired', 'cancelled'])
                        ->get();

                    $viableResCount = count($viableCustomerReservations) + count($viable3pReservations) + 1;

                    if ($viableResCount >= $reservation->promoCode->usage_limit){

                        return redirect()->back()->with('flash_error', 'Promo code reservation limit reached.');
                    }

                    $reservation->promoCode()->update([
                        'reservations_count' => DB::raw('reservations_count + 1')
                    ]);
                }
            }

            $reservation->fill($validated);
            $reservation->save();
            $referer = ThirdPartiesPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid) ??
            CustomersPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid) ??
            ReferralsPromoCodeReservation::withoutGlobalScopes()->find($reservation->referer_uuid);

            DB::commit();

            $this->sendSms(auth()->user()->ins, $validated['phone'], "From: {$referer->name} | ".$validated['message'] . ". Here is your redeemable promo code '{$reservation->redeemable_code}' - click and claim it here: " . route('generate-promo-code-banner', $reservation->uuid));

            $this->sendEmail(
                $validated['email'],
                'A Promotional Code Reservation Has Been Created for You',

                "
                    <p style='margin-bottom: 16px'>Dear {$reservation['name']},</p>
                    
                    <p style='margin-bottom: 20px'> {$reservation->message} </p>  
                    
                " .
                $this->generatePromoCodeBanner($reservation->uuid),
                Auth::user()->ins
            );


        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.show-reserve-referral-promo-code', $resId), ['flash_success' => "Promo code reservation updated successfully."]);

    }



    public function getCustomerReservationsData(Request $request) {

        // Extract and restructure productIds
        $productIds = collect($request->input('productIds'))->pluck('value')->toArray();
        $productQuantities = collect($request->input('productQuantities'))->pluck('value')->toArray();
        $productPrices = collect($request->input('productPrices'))->pluck('value')->toArray();
        $productTaxRates = collect($request->input('productTaxRates'))->pluck('value')->toArray();

        Log::info($productTaxRates);

        // Merge the restructured data back into the request
        $request->merge([
            'productIds' => $productIds,
            'productQuantities' => $productQuantities,
            'productPrices' => $productPrices,
            'productTaxRates' => $productTaxRates,
        ]);


        Log::info(json_encode($request->toArray()));

        if (empty(request('reservationUuid')) && empty(request('customerId'))) return false;

        try {

            $customerReservations = CustomersPromoCodeReservation::
//                whereHas('promoCode', function ($promo){
//                   $promo->where('promo_type', '!=', 'description_promo');
//                })
//                ->
                when(request('reservationUuid'), function ($query) {
                    $query->where('uuid', request('reservationUuid'));
                })
                ->when(request('customerId'), function ($query) {
                    $query->where('customer_id', request('customerId'));
                })
                ->where('status', 'reserved')
                ->get()
                ->map(function ($reservation) {

                    $codeMetaData = (new PromotionalCodeController())->getActiveReservableCodesMetadata($reservation->promo_code_id)->first();

                    $productsData = [];

                    if (request('productIds')) {

                        $productIds = PromotionalCodeProduct::where('promotional_code_id', $codeMetaData->id)->get()->pluck('product_variation_id');
                        $categoryIds = PromotionalCodeProductCategory::where('promotional_code_id', $codeMetaData->id)->get()->pluck('product_category_id');

                        $allProductIds = ProductVariation::withoutGlobalScopes()
                            ->whereIn('id', $productIds)
                            ->orWhereIn('productcategory_id', $categoryIds)
                            ->get()
                            ->pluck('id')
                            ->toArray();

                        $targetIds = array_values(array_intersect($allProductIds, request('productIds')));

                        $targetProducts = [];

                        foreach (request('productIds') as $index => $id) {

                            if (in_array($id, $targetIds)) {

                                $prod = ProductVariation::withoutGlobalScopes()
                                    ->find($id);
                                $prod->invoice_price = request('productPrices')[$index];
                                $prod->invoice_tax_rate = request('productTaxRates') ? request('productTaxRates')[$index] : 0;

                                if ($prod) array_push($targetProducts, $prod);
                            }
                        }


                        if (count($targetProducts) > 0) {

                            $productsData = collect($targetProducts)
                                ->map(function ($product) use ($reservation) {

                                    $productQuantityKey = null;
                                    if (request('productIds') && request('productQuantities')) $productQuantityKey = array_combine(request('productIds'), request('productQuantities'));

                                    if ($productQuantityKey) $quantity = $productQuantityKey[$product->id];
                                    else $quantity = 1;

                                    $productPriceKey = null;
                                    if (request('productIds') && request('productPrices')) $productPriceKey = array_combine(request('productIds'), request('productPrices'));

                                    if ($productPriceKey) $price = floatval(str_replace(',', '', $product->invoice_price));
                                    else $price = 0;


                                    $discount = null;

                                    if ($reservation->promoCode->discount_type === 'fixed') {

                                        if ($reservation->tier == 1) $discount = $reservation->promoCode->discount_value;
                                        else $discount = $reservation->promoCode['discount_value_' . $reservation->tier];
                                    } else if ($reservation->promoCode->discount_type === 'percentage') {

                                        if ($reservation->tier == 1) $discount = bcmul(
                                            bcdiv($reservation->promoCode->discount_value, 100, 2),
                                            floatval(str_replace(',', '', $product->invoice_price))
                                            , 2);

                                        else $discount = bcmul(
                                            bcdiv($reservation->promoCode['discount_value_' . $reservation->tier], 100, 2),
                                            floatval(str_replace(',', '', $product->invoice_price))
                                            , 2);
                                    }

                                    Log::info($reservation);

                                    if ($reservation->tier == 1) $discountOffered = $reservation->promoCode['discount_value'];
                                    else if ($reservation->tier == 2) $discountOffered = $reservation->promoCode['discount_value_2'];
                                    else $discountOffered = $reservation->promoCode['discount_value_3'];

                                    $discountUnit = $reservation->promoCode->discount_type === 'percentage' ? '%' : '';


                                    if (intval($product->invoice_tax_rate)) $taxDecimal = bcdiv($product->invoice_tax_rate, 100, 2);
                                    else $taxDecimal = 0;

                                    return (object)[

                                        'product_id' => $product->id,
                                        'name' => $product->code . ' | ' . $product->name,
                                        'discount_type' => 'Tier ' . $reservation->tier . ' | ' . strtoupper($reservation->promoCode->discount_type),
                                        'discount_offered' => number_format($discountOffered,2) . $discountUnit,
                                        'price' => doubleval($price),
                                        'unit_discount' => doubleval($discount),
                                        'quantity' => doubleval($quantity),
                                        'discount' => doubleval(bcmul($discount, $quantity, 2)),
                                        'tax_rate' => doubleval($product->invoice_tax_rate),
                                        'discounted_tax' => doubleval(bcmul(bcmul($discount, $quantity, 2), $taxDecimal, 2)),
                                    ];
                                }); // Convert to array

                        }

                    }

                    $discountUnit = $reservation->promoCode->discount_type === 'percentage' ? '%' : '';
                    $promoType = $reservation->promo_type === 'specific_products' ? 'Offers on Specific Products' : 'Offers on Product Categories';

                    return (object)[

                        'uuid' => $reservation->uuid,
                        'label' => $reservation->redeemable_code . ' | Promo Code - ' . $codeMetaData->code . ' | ' . $promoType . ' | ' . $codeMetaData->discount_type . ' | ' . number_format($codeMetaData->discount_value, 2) . $discountUnit . ', ' . number_format($codeMetaData->discount_value_2, 2) . $discountUnit . ', ' . number_format($codeMetaData->discount_value_3, 2) . $discountUnit . ' | ' . $codeMetaData->period,
                        'discounts' => $productsData,
                        'discountsTable' => $this->generateDiscountsTable($productsData)
                    ];
                });



            $thirdPartyReservations = ThirdPartiesPromoCodeReservation::
                whereHas('promoCode', function ($promo){
                    $promo->where('promo_type', '!=', 'description_promo');
                })
                ->when(request('reservationUuid'), function ($query) {
                    $query->where('uuid', request('reservationUuid'));
                })
                ->when(request('customerId'), function ($query) {
                    $query->where('customer_id', request('customerId'));
                })
                ->where('status', 'reserved')
                ->get()
                ->map(function ($reservation) {

                    $codeMetaData = (new PromotionalCodeController())->getActiveReservableCodesMetadata($reservation->promo_code_id)->first();

                    $productsData = [];

                    if (request('productIds')) {

                        $productIds = PromotionalCodeProduct::where('promotional_code_id', $codeMetaData->id)->get()->pluck('product_variation_id');
                        $categoryIds = PromotionalCodeProductCategory::where('promotional_code_id', $codeMetaData->id)->get()->pluck('product_category_id');

                        $allProductIds = ProductVariation::withoutGlobalScopes()
                            ->whereIn('id', $productIds)
                            ->orWhereIn('productcategory_id', $categoryIds)
                            ->get()
                            ->pluck('id')
                            ->toArray();

                        $targetIds = array_values(array_intersect($allProductIds, request('productIds')));

                        $targetProducts = [];

                        foreach (request('productIds') as $index => $id) {

                            if (in_array($id, $targetIds)) {

                                $prod = ProductVariation::withoutGlobalScopes()
                                    ->find($id);
                                $prod->invoice_price = request('productPrices')[$index];
                                $prod->invoice_tax_rate = request('productTaxRates') ? request('productTaxRates')[$index] : 0;

                                if ($prod) array_push($targetProducts, $prod);
                            }
                        }


                        if (count($targetProducts) > 0) {

                            $productsData = collect($targetProducts)
                                ->map(function ($product) use ($reservation) {

                                    $productQuantityKey = null;
                                    if (request('productIds') && request('productQuantities')) $productQuantityKey = array_combine(request('productIds'), request('productQuantities'));

                                    if ($productQuantityKey) $quantity = $productQuantityKey[$product->id];
                                    else $quantity = 1;

                                    $productPriceKey = null;
                                    if (request('productIds') && request('productPrices')) $productPriceKey = array_combine(request('productIds'), request('productPrices'));

                                    if ($productPriceKey) $price = floatval(str_replace(',', '', $product->invoice_price));
                                    else $price = 0;


                                    $discount = null;

                                    if ($reservation->promoCode->discount_type === 'fixed') {

                                        if ($reservation->tier == 1) $discount = $reservation->promoCode->discount_value;
                                        else $discount = $reservation->promoCode['discount_value_' . $reservation->tier];
                                    } else if ($reservation->promoCode->discount_type === 'percentage') {

                                        if ($reservation->tier == 1) $discount = bcmul(
                                            bcdiv($reservation->promoCode->discount_value, 100, 2),
                                            floatval(str_replace(',', '', $product->invoice_price))
                                            , 2);

                                        else $discount = bcmul(
                                            bcdiv($reservation->promoCode['discount_value_' . $reservation->tier], 100, 2),
                                            floatval(str_replace(',', '', $product->invoice_price))
                                            , 2);
                                    }

                                    Log::info($reservation);

                                    if ($reservation->tier == 1) $discountOffered = $reservation->promoCode['discount_value'];
                                    else if ($reservation->tier == 2) $discountOffered = $reservation->promoCode['discount_value_2'];
                                    else $discountOffered = $reservation->promoCode['discount_value_3'];

                                    $discountUnit = $reservation->promoCode->discount_type === 'percentage' ? '%' : '';


                                    if (intval($product->invoice_tax_rate)) $taxDecimal = bcdiv($product->invoice_tax_rate, 100, 2);
                                    else $taxDecimal = 0;

                                    return (object)[

                                        'product_id' => $product->id,
                                        'name' => $product->code . ' | ' . $product->name,
                                        'discount_type' => 'Tier ' . $reservation->tier . ' | ' . strtoupper($reservation->promoCode->discount_type),
                                        'discount_offered' => number_format($discountOffered,2) . $discountUnit,
                                        'price' => doubleval($price),
                                        'unit_discount' => doubleval($discount),
                                        'quantity' => doubleval($quantity),
                                        'discount' => doubleval(bcmul($discount, $quantity, 2)),
                                        'tax_rate' => doubleval($product->invoice_tax_rate),
                                        'discounted_tax' => doubleval(bcmul(bcmul($discount, $quantity, 2), $taxDecimal, 2)),
                                    ];
                                }); // Convert to array

                        }

                    }

                    $discountUnit = $reservation->promoCode->discount_type === 'percentage' ? '%' : '';
                    $promoType = $reservation->promo_type === 'specific_products' ? 'Offers on Specific Products' : 'Offers on Product Categories';

                    return (object)[

                        'uuid' => $reservation->uuid,
                        'label' => $reservation->redeemable_code . ' | Promo Code - ' . $codeMetaData->code . ' | ' . $promoType . ' | ' . $codeMetaData->discount_type . ' | ' . number_format($codeMetaData->discount_value, 2) . $discountUnit . ', ' . number_format($codeMetaData->discount_value_2, 2) . $discountUnit . ', ' . number_format($codeMetaData->discount_value_3, 2) . $discountUnit . ' | ' . $codeMetaData->period,
                        'discounts' => $productsData,
                        'discountsTable' => $this->generateDiscountsTable($productsData)
                    ];
                });


            $referralReservations = ReferralsPromoCodeReservation::
                whereHas('promoCode', function ($promo){
                    $promo->where('promo_type', '!=', 'description_promo');
                })
                ->when(request('reservationUuid'), function ($query) {
                    $query->where('uuid', request('reservationUuid'));
                })
                ->when(request('customerId'), function ($query) {
                    $query->where('customer_id', request('customerId'));
                })
                ->where('status', 'reserved')
                ->get()
                ->map(function ($reservation) {

                    $codeMetaData = (new PromotionalCodeController())->getActiveReservableCodesMetadata($reservation->promo_code_id)->first();

                    $productsData = [];

                    if (request('productIds')) {

                        $productIds = PromotionalCodeProduct::where('promotional_code_id', $codeMetaData->id)->get()->pluck('product_variation_id');
                        $categoryIds = PromotionalCodeProductCategory::where('promotional_code_id', $codeMetaData->id)->get()->pluck('product_category_id');

                        $allProductIds = ProductVariation::withoutGlobalScopes()
                            ->whereIn('id', $productIds)
                            ->orWhereIn('productcategory_id', $categoryIds)
                            ->get()
                            ->pluck('id')
                            ->toArray();

                        $targetIds = array_values(array_intersect($allProductIds, request('productIds')));

                        $targetProducts = [];

                        foreach (request('productIds') as $index => $id) {

                            if (in_array($id, $targetIds)) {

                                $prod = ProductVariation::withoutGlobalScopes()
                                    ->find($id);
                                $prod->invoice_price = request('productPrices')[$index];
                                $prod->invoice_tax_rate = request('productTaxRates') ? request('productTaxRates')[$index] : 0;

                                if ($prod) array_push($targetProducts, $prod);
                            }
                        }


                        if (count($targetProducts) > 0) {

                            $productsData = collect($targetProducts)
                                ->map(function ($product) use ($reservation) {

                                    $productQuantityKey = null;
                                    if (request('productIds') && request('productQuantities')) $productQuantityKey = array_combine(request('productIds'), request('productQuantities'));

                                    if ($productQuantityKey) $quantity = $productQuantityKey[$product->id];
                                    else $quantity = 1;

                                    $productPriceKey = null;
                                    if (request('productIds') && request('productPrices')) $productPriceKey = array_combine(request('productIds'), request('productPrices'));

                                    if ($productPriceKey) $price = floatval(str_replace(',', '', $product->invoice_price));
                                    else $price = 0;


                                    $discount = null;

                                    if ($reservation->promoCode->discount_type === 'fixed') {

                                        if ($reservation->tier == 1) $discount = $reservation->promoCode->discount_value;
                                        else $discount = $reservation->promoCode['discount_value_' . $reservation->tier];
                                    } else if ($reservation->promoCode->discount_type === 'percentage') {

                                        if ($reservation->tier == 1) $discount = bcmul(
                                            bcdiv($reservation->promoCode->discount_value, 100, 2),
                                            floatval(str_replace(',', '', $product->invoice_price))
                                            , 2);

                                        else $discount = bcmul(
                                            bcdiv($reservation->promoCode['discount_value_' . $reservation->tier], 100, 2),
                                            floatval(str_replace(',', '', $product->invoice_price))
                                            , 2);
                                    }

                                    Log::info($reservation);

                                    if ($reservation->tier == 1) $discountOffered = $reservation->promoCode['discount_value'];
                                    else if ($reservation->tier == 2) $discountOffered = $reservation->promoCode['discount_value_2'];
                                    else $discountOffered = $reservation->promoCode['discount_value_3'];

                                    $discountUnit = $reservation->promoCode->discount_type === 'percentage' ? '%' : '';


                                    if (intval($product->invoice_tax_rate)) $taxDecimal = bcdiv($product->invoice_tax_rate, 100, 2);
                                    else $taxDecimal = 0;

                                    return (object)[

                                        'product_id' => $product->id,
                                        'name' => $product->code . ' | ' . $product->name,
                                        'discount_type' => 'Tier ' . $reservation->tier . ' | ' . strtoupper($reservation->promoCode->discount_type),
                                        'discount_offered' => number_format($discountOffered,2) . $discountUnit,
                                        'price' => doubleval($price),
                                        'unit_discount' => doubleval($discount),
                                        'quantity' => doubleval($quantity),
                                        'discount' => doubleval(bcmul($discount, $quantity, 2)),
                                        'tax_rate' => doubleval($product->invoice_tax_rate),
                                        'discounted_tax' => doubleval(bcmul(bcmul($discount, $quantity, 2), $taxDecimal, 2)),
                                    ];
                                }); // Convert to array

                        }

                    }

                    $discountUnit = $reservation->promoCode->discount_type === 'percentage' ? '%' : '';
                    $promoType = $reservation->promo_type === 'specific_products' ? 'Offers on Specific Products' : 'Offers on Product Categories';

                    return (object)[

                        'uuid' => $reservation->uuid,
                        'label' => $reservation->redeemable_code . ' | Promo Code - ' . $codeMetaData->code . ' | ' . $promoType . ' | ' . $codeMetaData->discount_type . ' | ' . number_format($codeMetaData->discount_value, 2) . $discountUnit . ', ' . number_format($codeMetaData->discount_value_2, 2) . $discountUnit . ', ' . number_format($codeMetaData->discount_value_3, 2) . $discountUnit . ' | ' . $codeMetaData->period,
                        'discounts' => $productsData,
                        'discountsTable' => $this->generateDiscountsTable($productsData)
                    ];
                });


            if (request('customerId') && !request('reservationUuid')) {

                Log::info(response()->json(array_merge($customerReservations->toArray(), $thirdPartyReservations->toArray(), $referralReservations->toArray())));

                return response()->json(array_merge($customerReservations->toArray(), $thirdPartyReservations->toArray(), $referralReservations->toArray()));
            }

            else {

                $reservationsData = $customerReservations->first() ?? $thirdPartyReservations->first() ?? $referralReservations->first();

//            return request('productIds');

//            return $customerReservations->toArray() ?? $thirdPartyReservations->toArray() ?? $referralReservations->toArray();

                $overallDiscount = $reservationsData->discounts->pluck('discount')->sum();
                $overallDiscountedTax = $reservationsData->discounts->pluck('discounted_tax')->sum();

                Log::info(response()->json(compact('reservationsData', 'overallDiscount')));

                return response()->json(compact('reservationsData', 'overallDiscount', 'overallDiscountedTax'));
            }

        }
        catch (Exception $ex) {
            // Log any exception that occurs
            Log::error(
                json_encode([
                    'message' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine(),
                ])
            );
        }
    }


    function generateDiscountsTable($data) {

        if (count($data) < 1) return false;

        $totalQuantity = 0;
        $totalDiscount = 0;
        $totalDiscountedTax = 0;

        $html = '<h3>Affinite Program Discounts</h3> <table border="1" style="width:100%; border-collapse:collapse;">';
        $html .= '<thead>
        <tr style="background-color:#f2f2f2;">
            <th style="padding: 10px;">Name</th>
            <th style="padding: 10px;">Discount Type</th>
            <th style="padding: 10px;">Discount Offered</th>
            <th style="padding: 10px; text-align:right;">Price</`th>
            <th style="padding: 10px; text-align:right;">Unit Discount</th>
            <th style="padding: 10px; text-align:right;">Quantity</th>
            <th style="padding: 10px; text-align:right;">Discount</th>
            <th style="padding: 10px; text-align:right;">Tax Rate</th>
            <th style="padding: 10px; text-align:right;">Discounted Tax</th>
        </tr>
    </thead>';

        $html .= '<tbody>';
        foreach ($data as $index => $item) {
            $rowColor = $index % 2 === 0 ? 'background-color:#ffffff;' : 'background-color:#f9f9f9;';
            $html .= '<tr style="' . $rowColor . '">
            <td style="padding: 10px;">' . htmlspecialchars($item->name) . '</td>
            <td style="padding: 10px;">' . htmlspecialchars($item->discount_type) . '</td>
            <td style="padding: 10px;">' . htmlspecialchars($item->discount_offered) . '</td>
            <td style="padding: 10px; text-align:right;">' . number_format($item->price,2) . '</td>
            <td style="padding: 10px; text-align:right;">' . number_format($item->unit_discount, 2) . '</td>
            <td style="padding: 10px; text-align:right;">' . number_format($item->quantity, 2) . '</td>
            <td style="padding: 10px; text-align:right;">' . number_format($item->discount, 2) . '</td>
            <td style="padding: 10px; text-align:right;">' . number_format($item->tax_rate, 2) . '</td>
            <td style="padding: 10px; text-align:right;">' . number_format($item->discounted_tax, 2) . '</td>
        </tr>';

            $totalQuantity += $item->quantity;
            $totalDiscount += $item->discount;
            $totalDiscountedTax += $item->discounted_tax;
        }
        $html .= '</tbody>';

        // Add totals row
        $html .= '<tfoot>
        <tr style="background-color:#e6e6e6; font-weight:bold;">
            <td colspan="5" style="padding: 10px; text-align:right;">Totals:</td>
            <td style="padding: 10px; text-align:right; font-size: 14px;">' . number_format($totalQuantity, 2) . '</td>
            <td style="padding: 10px; text-align:right; font-size: 14px;">' . number_format($totalDiscount, 2) . '</td>
            <td style="padding: 10px; text-align:right; font-size: 14px;"> </td>
            <td style="padding: 10px; text-align:right; font-size: 14px;">' . number_format($totalDiscountedTax, 2) . '</td>

        </tr>
    </tfoot>';

        $html .= '</table>';

        return $html;
    }



    public function sendEmail($email, $subject, $content, $companyId){


        try {

            DB::beginTransaction();

            $email_input = [
                'text' => $content,
                'subject' => $subject,
                // 'email' => $others,
                'mail_to' => $email,
            ];


            $email = (new RosemailerRepository($companyId))->send($email_input['text'], $email_input);
            $email_output = json_decode($email);
            if ($email_output->status === "Success") {



                $email_data = [
                    'text_email' => $email_input['text'],
                    'subject' => $email_input['subject'],
                    'user_emails' => $email_input['mail_to'],
                    'ins' => $companyId,
                    'user_id' => User::withoutGlobalScopes()->where('ins', $companyId)->where('status', 1)->first()->id,
                    'status' => 'sent'
                ];
                SendEmail::create($email_data);

            }

            DB::commit();
        }
        catch (Exception $ex){

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.recent-customer-messages'), ['flash_success' => "Customer Email Sent Successfully."]);
    }


    public function sendSms($ins, $phoneNumber, $content){


        try {

            DB::beginTransaction();

            $cost_per_160 = 0.6;
            $charCount = strlen($content);
            $send_sms = new SendSms();

            $send_sms->subject = $content;
            $send_sms->phone_numbers = $phoneNumber;
            $send_sms->user_type = 'customer';
            $send_sms->delivery_type = 'now';
            $send_sms->message_type = 'single';
            $send_sms->sent_to_ids = '';
            $send_sms->characters = $charCount;
            $send_sms->cost = $cost_per_160;
            $send_sms->user_count = 1;
            $send_sms->total_cost = $cost_per_160 * ceil($charCount / 160);
            if (auth()->user()) {

                $send_sms->user_id = auth()->user()->id;
                $send_sms->ins = auth()->user()->ins;

                $send_sms->save();
                (new RosesmsRepository(auth()->user()->ins))->bulk_sms($phoneNumber, $content, $send_sms);
            }else{
                $send_sms->user_id = $ins;
                $send_sms->ins = $ins;

                $send_sms->save();
                (new RosesmsRepository($ins))->bulk_sms($phoneNumber, $content, $send_sms);
            }


            DB::commit();
        }
        catch (Exception $ex){

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

    }



    public function contactUs(Request $request, $resUuid)
    {

        $companyId = $request->input('companyId');
        return $internalPromoCodeBannerHtml = $this->generatePromoCodeBanner($resUuid, true);

        //TODO: ADD CODE FOR SEND EMAIL TO BUSINESS EMPLOYEES WITH INCLUDED BANNER
    }


    public function generateRedeemableCode()
    {

        $randomPart = Str::upper(Str::random(8));

        return $randomPart;
    }

    public function getReferralsTable() {


        $customersReservations = CustomersPromoCodeReservation::
            orderBy('reserved_at', 'desc')
            ->when(request('promoCodeFilter'), function($query) {

                $query->whereHas('promoCode', function ($pC) {
                    $pC->where('id', request('promoCodeFilter'));
                });

            })
            ->when(request('tierFilter'), function ($query) {

                $query->where('tier', request('tierFilter'));
            })
            ->when(request('fromDateFilter'), function ($query) {

                $fromDate = (new DateTime((request('fromDateFilter'))))->format('Y-m-d');

                $query->whereDate('reserved_at', '>=', $fromDate);
            })
            ->when(request('toDateFilter'), function ($query) {

                $toDate = (new DateTime((request('toDateFilter'))))->format('Y-m-d');

                $query->whereDate('reserved_at', '<=', $toDate);
            })
            ->get()
            ->map(function ($reservation) {

                $showButton =  '<a href="' . route('biller.show-reserve-customer-promo-code', $reservation->uuid) . '" target="_blank" class="btn btn-secondary round mr-1">View</a>';



                return [

                    'redeemable_code' => $reservation->redeemable_code,
                    'name' => optional($reservation->customer)->company,
                    'date' => (new DateTime($reservation->reserved_at))->format('d/m/Y'),
                    'promo_code' => $reservation->promoCode->code,
                    'contact' => $reservation->phone . ' | ' . $reservation->email,
                    'tier_3' => $this->getRefererChain($reservation->uuid, 3),
                    'tier_2' => $this->getRefererChain($reservation->uuid, 2),
                    'tier_1' => $this->getRefererChain($reservation->uuid, 1),
                    'actions' => $showButton,
                ];

            })
            ->toArray();


        $thirdPartiesReservations = ThirdPartiesPromoCodeReservation::
            orderBy('reserved_at', 'desc')
            ->when(request('promoCodeFilter'), function($query) {

                $query->whereHas('promoCode', function ($pC) {
                    $pC->where('id', request('promoCodeFilter'));
                });

            })
            ->when(request('tierFilter'), function ($query) {

                $query->where('tier', request('tierFilter'));
            })
            ->when(request('fromDateFilter'), function ($query) {

                $fromDate = (new DateTime((request('fromDateFilter'))))->format('Y-m-d');

                $query->whereDate('reserved_at', '>=', $fromDate);
            })
            ->when(request('toDateFilter'), function ($query) {

                $toDate = (new DateTime((request('toDateFilter'))))->format('Y-m-d');

                $query->whereDate('reserved_at', '<=', $toDate);
            })
            ->get()
            ->map(function ($reservation) {

                $showButton =  '<a href="' . route('biller.show-reserve-3p-promo-code', $reservation->uuid) . '" target="_blank" class="btn btn-secondary round mr-1">View</a>';



                return [

                    'redeemable_code' => $reservation->redeemable_code,
                    'name' => $reservation->name,
                    'date' => (new DateTime($reservation->reserved_at))->format('d/m/Y'),
                    'promo_code' => $reservation->promoCode->code,
                    'contact' => $reservation->phone . ' | ' . $reservation->email,
                    'tier_3' => $this->getRefererChain($reservation->uuid, 3),
                    'tier_2' => $this->getRefererChain($reservation->uuid, 2),
                    'tier_1' => $this->getRefererChain($reservation->uuid, 1),
                    'actions' => $showButton,
                ];

            })
            ->toArray();


        $referralsReservations = ReferralsPromoCodeReservation::
            orderBy('reserved_at', 'desc')
            ->when(request('promoCodeFilter'), function($query) {

                $query->whereHas('promoCode', function ($pC) {
                    $pC->where('id', request('promoCodeFilter'));
                });

            })
            ->when(request('tierFilter'), function ($query) {

                $query->where('tier', request('tierFilter'));
            })
            ->when(request('fromDateFilter'), function ($query) {

                $fromDate = (new DateTime((request('fromDateFilter'))))->format('Y-m-d');

                $query->whereDate('reserved_at', '>=', $fromDate);
            })
            ->when(request('toDateFilter'), function ($query) {

                $toDate = (new DateTime((request('toDateFilter'))))->format('Y-m-d');

                $query->whereDate('reserved_at', '<=', $toDate);
            })
            ->get()
            ->map(function ($reservation) {

                $showButton =  '<a href="' . route('biller.show-reserve-referral-promo-code', $reservation->uuid) . '" target="_blank" class="btn btn-secondary round mr-1">View</a>';



                return [

                    'redeemable_code' => $reservation->redeemable_code,
                    'name' => $reservation->name,
                    'date' => (new DateTime($reservation->reserved_at))->format('d/m/Y'),
                    'promo_code' => $reservation->promoCode->code,
                    'contact' => $reservation->phone . ' | ' . $reservation->email,
                    'tier_3' => $this->getRefererChain($reservation->uuid, 3),
                    'tier_2' => $this->getRefererChain($reservation->uuid, 2),
                    'tier_1' => $this->getRefererChain($reservation->uuid, 1),
                    'actions' => $showButton,
                ];

            })
            ->toArray();



        $referrals = array_merge($customersReservations, $thirdPartiesReservations, $referralsReservations);

        return Datatables::of($referrals)
            ->rawColumns(['actions', 'tier_1', 'tier_2', 'tier_3',])
            ->make(true);
    }



    public function getRefererChain($resUuid, $tier) {

        if (ThirdPartiesPromoCodeReservation::find($resUuid)) $showRoute = route('biller.show-reserve-3p-promo-code', $resUuid);
        else if (CustomersPromoCodeReservation::find($resUuid)) $showRoute = route('biller.show-reserve-customer-promo-code', $resUuid);
        else if (ReferralsPromoCodeReservation::find($resUuid)) $showRoute = route('biller.show-reserve-referral-promo-code', $resUuid);


        $referer = ThirdPartiesPromoCodeReservation::find($resUuid) ??
            CustomersPromoCodeReservation::find($resUuid) ??
            ReferralsPromoCodeReservation::find($resUuid);

        $refererName = null;

        if ($referer) {

            $redeemableCode = '<a href="' . $showRoute . '" target="_blank">' . ($referer->redeemable_code ?? "PRE-CODE RESERVATION") . '</a>';

            if ($referer->customer_id) {

                $cust = Customer::find($referer->customer_id);

                if($cust) $refererName = $redeemableCode . ' | ' . $cust->company . ' || ' . $cust->phone . ' | ' . $cust->email;
            }
            else if ($referer->name === $referer->organization) $refererName = $redeemableCode . ' | ' .  $referer->name . ' || ' . $referer->phone . ' | ' . $referer->email;
            else $refererName = $redeemableCode . ' | ' . $referer->name . ' | ' . $referer->organization . ' || ' . $referer->phone . ' | ' . $referer->email;
        }


        if ($referer->tier == $tier) $refererChain = [$refererName];
        else $refererChain = [];

        $parentReferer = $referer;

        while ($parentReferer){

            $customerReferer = CustomersPromoCodeReservation::where('tier', $tier)->where('uuid', $parentReferer->referer_uuid)->first();

            $otherReferer = ThirdPartiesPromoCodeReservation::where('tier', $tier)->where('uuid', $parentReferer->referer_uuid)->first() ??
                ReferralsPromoCodeReservation::where('tier', $tier)->where('uuid', $parentReferer->referer_uuid)->first();

            $parentRefererName = null;

            if ($customerReferer) {

                $customer = Customer::find($customerReferer->customer_id);

                $redeemableCode = '<a href="' . $showRoute . '" target="_blank">' . ($customerReferer->redeemable_code ?? "PRE-CODE RESERVATION") . '</a>';

                $parentRefererName = $redeemableCode . ' | ' . $customer->company . ' || ' . $customer->phone . ' | ' . $customer->email;

                array_push($refererChain, $parentRefererName);
                $parentReferer = $customerReferer;
            }
            else if ($otherReferer){

                $redeemableCode = '<a href="' . $showRoute . '" target="_blank">' . ($otherReferer->redeemable_code ?? "PRE-CODE RESERVATION") . '</a>';

                if ($otherReferer->name !== $otherReferer->organization) $parentRefererName = $redeemableCode . ' | ' . $otherReferer->name . ' | ' . $otherReferer->organization . ' || ' . $otherReferer->phone . ' | ' . $otherReferer->email;
                else $parentRefererName = $redeemableCode . ' | ' . $otherReferer->name . ' || ' . $otherReferer->phone . ' | ' . $otherReferer->email;

                array_push($refererChain, $parentRefererName);
                $parentReferer = $otherReferer;
            }
            else $parentReferer = null;
        }

//        return $refererChain;

        if  (!$refererChain) return null;

        return collect($refererChain)->map(fn($item) => "<span>{$item}</span>")->implode('<br><hr>');
    }

    public function referralsIndex(){

        $promoCodes = PromotionalCode::select('id', 'code')->get();

        return view('focus.promotional_code_reservations.referrals-index', compact('promoCodes'));
    }

    public function index_commission()
    {
        return view('focus.promotional_code_reservations.index_commission');
    }

    public function get_reservations(Request $request)
    {
        $source = $request->get('source'); // 'quote', 'invoice', or null (both)
        $paidOnly = $request->get('paid');  // e.g. "paid"

        // Collect reservation UUIDs from quotes
        $quote_reservation_ids = [];
        if (!$source || $source === 'quote') {
            $quotes = Quote::with('lead')->get();
            foreach ($quotes as $quote) {
                if ($quote->lead && $quote->lead->reservation_uuid) {
                    $quote_reservation_ids[] = $quote->lead->reservation_uuid;
                }
            }
        }

        // Collect reservation UUIDs from invoices
        $invoice_reservation_ids = [];
        if (!$source || $source === 'invoice') {
            $invoice_reservation_ids = Invoice::whereNotNull('reservation')
                ->pluck('reservation')
                ->toArray();
        }

        // Merge all reservation UUIDs
        $all_reservation_ids = array_merge($quote_reservation_ids, $invoice_reservation_ids);

        $models = [
            ThirdPartiesPromoCodeReservation::class,
            CustomersPromoCodeReservation::class,
            ReferralsPromoCodeReservation::class,
        ];

        $reservations = collect();

        // Fetch all reservations with eager-loaded commission > bill
        foreach ($models as $model) {
            $filtered = $model::whereIn('uuid', $all_reservation_ids)
                ->with(['commission_item.commission.bill'])
                ->get();

            $reservations = $reservations->merge($filtered);
        }

        // Build data array
        $data = [];

        foreach ($reservations as $reservation) {
            if ($reservation->tier == 3) {
                $tier_2 = $reservation->referralReferer;
                if ($tier_2) {
                    $commission = $tier_2->promoCode->commision_type === 'fixed'
                        ? amountFormat($tier_2->promoCode->cash_back_2)
                        : $tier_2->promoCode->cash_back_2 . '%';

                    $data[] = $this->formatReferrerData($tier_2, $commission);

                    $tier_1 = $this->getReferrer($tier_2);
                    if ($tier_1) {
                        $commission = $tier_1->promoCode->commision_type === 'fixed'
                            ? amountFormat($tier_1->promoCode->cash_back_3)
                            : $tier_1->promoCode->cash_back_3 . '%';

                        $data[] = $this->formatReferrerData($tier_1, $commission);
                    }
                }
            } elseif ($reservation->tier == 2) {
                $tier_1 = $this->getReferrer($reservation);
                if ($tier_1) {
                    $commission = $tier_1->promoCode->commision_type === 'fixed'
                        ? amountFormat($tier_1->promoCode->cash_back_1)
                        : $tier_1->promoCode->cash_back_1 . '%';

                    $data[] = $this->formatReferrerData($tier_1, $commission);
                }
            }
        }

        // Filter $data[] by paidOnly if provided
        if ($paidOnly) {
            $data = array_filter($data, function ($row) use ($paidOnly) {
                $commissionItem = CommissionItem::where('reserve_uuid', $row['uuid'])
                    ->with(['commission.bill'])
                    ->first();

                $status = optional($commissionItem->commission->bill ?? null)->status ?? '';
                return strtolower(trim($status)) === strtolower(trim($paidOnly));
            });
        }

        // Return DataTables
        return DataTables::of($data)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('checkbox', function ($r) use ($source) {
                $commissionItem = CommissionItem::where('reserve_uuid', $r['uuid'])->first();

                if ($source == 'invoice' && !$commissionItem) {
                    return '<input type="checkbox" class="select-row" value="' . $r['uuid'] . '">';
                }
                return '<input checked disabled type="checkbox" class="select-row" value="' . $r['uuid'] . '">';
            })
            ->addColumn('status', function ($r) {
                $commissionItem = CommissionItem::where('reserve_uuid', $r['uuid'])
                    ->with(['commission.bill'])
                    ->first();

                $status = optional($commissionItem->commission->bill ?? null)->status ?? '';

                $color = '';
                if (strtolower(trim($status)) === 'paid') {
                    $color = 'color: green; font-weight: bold;';
                } elseif (!empty($status)) {
                    $color = 'color: red; font-weight: bold;';
                }

                return "<span style='{$color}'>" . e($status) . "</span>";
            })
            ->addColumn('name', fn($r) => $r['name'])
            ->addColumn('email', fn($r) => $r['email'])
            ->addColumn('phone', fn($r) => $r['phone'])
            ->addColumn('tier', fn($r) => $r['tier'])
            ->addColumn('redeemable_code', fn($r) => $r['redeemable_code'])
            ->addColumn('commision', fn($r) => $r['commision'])
            ->rawColumns(['checkbox', 'status'])
            ->make(true);
    }

    private function getReferrer($entity)
    {
        return $entity->customerReferer ?? $entity->thirdPartyReferer ?? null;
    }

    private function formatReferrerData($referrer, $commission)
    {
        return [
            'promo_code_id' => $referrer->promo_code_id,
            'uuid' => $referrer->uuid,
            'name' => $referrer->name,
            'email' => $referrer->email,
            'phone' => $referrer->phone,
            'tier' => $referrer->tier,
            'redeemable_code' => $referrer->redeemable_code,
            'commision' => $commission,
        ];
    }



    public function create_commision_pay(Request $request)
    {
        $uuids = explode(',', $request->input('uuids'));
        $models = [
            ThirdPartiesPromoCodeReservation::class,
            CustomersPromoCodeReservation::class,
            ReferralsPromoCodeReservation::class,
        ];

        $reservations = collect();

       foreach ($models as $model) {
            $filtered = $model::whereIn('uuid', $uuids)
                ->get();

            $reservations = $reservations->merge($filtered);
        }
        $reserves = [];


        foreach ($reservations as $reservation) {
            $reserves[] = $this->promo_reservation($reservation);
        }
        // dd($reserves);
        return view('focus.commissions.create', compact('reserves'));
    }

    private function promo_reservation($reservation){
        if($reservation->tier == 1){
            $tier1 = $reservation;
            $promoCode = $tier1->promoCode;
            $tier2 = ReferralsPromoCodeReservation::where('referer_uuid',$tier1->uuid)->first();
            if($tier2){
                $invoice = Invoice::where('reservation',$tier2->uuid)->first();
                if ($invoice) {
                    $data = $this->referrerData($tier1);
                    $total_commission_type = $promoCode->total_commission_type;
                    $total_commission = $promoCode->total_commission;
                    $commission_type = $promoCode->commision_type;
                    // dd($invoice, $data, $total_commission_type, $commission_type);
                    if($total_commission_type == 'percentage' && $commission_type == 'percentage')
                    {
                        $data['raw_commision'] = ($promoCode->cash_back_1/100) * $total_commission;
                        $data['commission'] = $data['raw_commision'] ."%";
                        $data['commision_type'] = 'percentage';
                    }
                    elseif ($total_commission_type == 'percentage' && $commission_type == 'fixed') {
                        $data['raw_commision'] = $promoCode->cash_back_1;
                        $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                        $data['commision_type'] = 'fixed';
                    }
                    elseif ($total_commission_type == 'fixed' && $commission_type == 'percentage') {
                        $data['raw_commision'] =  ($promoCode->cash_back_1/100) * $total_commission;
                        $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                        $data['commision_type'] = 'fixed';
                    }
                    elseif ($total_commission_type == 'fixed' && $commission_type == 'fixed') {
                        $data['raw_commision'] =  $promoCode->cash_back_1;
                        $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                        $data['commision_type'] = 'fixed';
                    }
                    $lead = Lead::where('reservation_uuid',$tier2->uuid)->first();
                    $quote = $lead->quotes()->first();
                    $data['invoice_id'] = $invoice->id;
                    $data['total'] = $invoice->subtotal;
                    $data['quote_id'] = $quote->id;
                    $data['quote_amount'] = $quote->subtotal;
                }else{
                    $tier3 = ReferralsPromoCodeReservation::where('referer_uuid',$tier2->uuid)->first();
                    $invoice = Invoice::where('reservation',$tier3->uuid)->first();
                    if($tier3 && $invoice){
                        $data = $this->referrerData($tier1);
                        $total_commission_type = $promoCode->total_commission_type;
                        $total_commission = $promoCode->total_commission;
                        $commission_type = $promoCode->commision_type;
                        if($total_commission_type == 'percentage' && $commission_type == 'percentage')
                        {
                            $data['raw_commision'] = ($promoCode->cash_back_3/100) * $total_commission;
                            $data['commission'] = $data['raw_commision'] ."%";
                            $data['commision_type'] = 'percentage';
                        }
                        elseif ($total_commission_type == 'percentage' && $commission_type == 'fixed') {
                            $data['raw_commision'] = $promoCode->cash_back_3;
                            $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                            $data['commision_type'] = 'fixed';
                        }
                        elseif ($total_commission_type == 'fixed' && $commission_type == 'percentage') {
                            $data['raw_commision'] =  ($promoCode->cash_back_3/100) * $total_commission;
                            $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                            $data['commision_type'] = 'fixed';
                        }
                        elseif ($total_commission_type == 'fixed' && $commission_type == 'fixed') {
                            $data['raw_commision'] =  $promoCode->cash_back_3;
                            $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                            $data['commision_type'] = 'fixed';
                        }
                        $lead = Lead::where('reservation_uuid',$tier3->uuid)->first();
                        $quote = $lead->quotes()->first();
                        $data['invoice_id'] = $invoice->id;
                        $data['total'] = $invoice->subtotal;
                        $data['quote_id'] = $quote->id;
                        $data['quote_amount'] = $quote->subtotal;
                    }
                }
            }
        }
        elseif ($reservation->tier == 2) {
            $tier3 = ReferralsPromoCodeReservation::where('referer_uuid',$reservation->uuid)->first();
            $invoice = Invoice::where('reservation',$tier3->uuid)->first();
            $data = $this->referrerData($reservation);
            $promoCode = $reservation->promoCode;
            $lead = Lead::where('reservation_uuid',$tier3->uuid)->first();
            $quote = $lead->quotes()->first();
            $data['invoice_id'] = $invoice->id;
            $data['total'] = $invoice->subtotal;
            $data['quote_id'] = $quote->id;
            $data['quote_amount'] = $quote->subtotal;
            $total_commission_type = $promoCode->total_commission_type;
            $total_commission = $promoCode->total_commission;
            $commission_type = $promoCode->commision_type;
            if($total_commission_type == 'percentage' && $commission_type == 'percentage')
            {
                $data['raw_commision'] = ($promoCode->cash_back_2/100) * $total_commission;
                $data['commission'] = $data['raw_commision'] ."%";
                $data['commision_type'] = 'percentage';
            }
            elseif ($total_commission_type == 'percentage' && $commission_type == 'fixed') {
                $data['raw_commision'] = $promoCode->cash_back_2;
                $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                $data['commision_type'] = 'fixed';
            }
            elseif ($total_commission_type == 'fixed' && $commission_type == 'percentage') {
                $data['raw_commision'] =  ($promoCode->cash_back_2/100) * $total_commission;
                $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                $data['commision_type'] = 'fixed';
            }
            elseif ($total_commission_type == 'fixed' && $commission_type == 'fixed') {
                $data['raw_commision'] =  $promoCode->cash_back_2;
                $data['commission'] = amountFormat($data['raw_commision'], $promoCode->currency_id);
                $data['commision_type'] = 'fixed';
            }
        }
        return $data;
    }

    private function referrerData($referrer)
    {
        return [
            'promo_code_id' => $referrer->promo_code_id,
            'uuid' => $referrer->uuid,
            'name' => $referrer->name,
            'email' => $referrer->email,
            'phone' => $referrer->phone,
            'tier' => $referrer->tier,
            'redeemable_code' => $referrer->redeemable_code,
        ];
    }

     public function storeForThirdParty(Request $request)
    {

        $validated = $request->validate([
            'promo_code_id' => ['required', 'exists:promotional_codes,id'],
            'tier' => ['required', 'in:1,2,3',

                function (string $attribute, $value, Closure $fail) use ($request) {

                    $promoCode = PromotionalCode::findOrFail(request('promo_code_id'));

                    if ($value == 1 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_1) {

                        $fail("Action Denied! Reservation Limit for tier 1 reached.");
                    }
                    else if ($value == 2 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_2) {

                        $fail("Action Denied! Reservation Limit for tier 2 reached.");
                    }
                    else if ($value == 3 && $this->getViableTierResCount($promoCode->id, $value) >= $promoCode->res_limit_3) {

                        $fail("Action Denied! Reservation Limit for tier 3 reached.");
                    }
                }
            ],
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['string', 'max:20', 'nullable'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            DB::beginTransaction();


            $promoCode = PromotionalCode::findOrFail($request->promo_code_id);

            $promoEnd = (new DateTime($promoCode->valid_until));
            $reservationEnd = (new DateTime())->add(new DateInterval('P' . $promoCode->reservation_period . 'D'));

            if ($promoEnd > $reservationEnd) $expiry = $reservationEnd;
            else $expiry = $promoEnd;
            $company = Company::find($promoCode->company_id);
            $discountValue = $promoCode->discount_value;
            $discount = $promoCode->discount_type == 'fixed' ? "KES {$discountValue}" : "{$discountValue} %";
            $validated['message'] = $this->sendPromotionMessage($validated['message'],$promoCode->description, $discount,$company->phone);

            $reservation = new ThirdPartiesPromoCodeReservation();
            $reservation->uuid = Str::uuid()->toString();
            $reservation->fill($validated);

            $reservation->fill([
                'reserved_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'expires_at' => $expiry->format('Y-m-d H:i:s'),
                'redeemable_code' => $this->generateRedeemableCode(),
            ]);

            $reservation->save();

            // Increment reservations count
            $promoCode->increment('reservations_count');

            DB::commit();   

            $this->sendSms($promoCode->company_id, $validated['phone'], "From: {$company->cname} | " . $validated['message'] . ". Here is your redeemable promo code '{$reservation->redeemable_code}' - click and claim it here: " . route('generate-promo-code-banner', $reservation->uuid) ." ");
            $this->sendEmail(
                $validated['email'],
                'A Promotional Code Reservation Has Been Created for You',

                "
                    <p style='margin-bottom: 16px'>Dear {$validated['name']},</p>                  
                 
                    <p style='margin-bottom: 20px'> {$reservation->message} </p>  
                " .
                $this->generatePromoCodeBanner($reservation->uuid),
                $promoCode->company_id
            );


        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return view('focus.promotional_code_reservations.self-enroll-success');
    }
}
