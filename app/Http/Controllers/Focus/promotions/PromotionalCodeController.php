<?php

namespace App\Http\Controllers\Focus\promotions;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\promotions\CustomersPromoCodeReservation;
use App\Models\promotions\PromotionalCode;
use App\Models\promotions\PromotionalCodeProduct;
use App\Models\promotions\PromotionalCodeProductCategory;
use App\Models\promotions\ReferralsPromoCodeReservation;
use App\Models\promotions\ThirdPartiesPromoCodeReservation;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use Carbon\Carbon;
use Closure;
use DateTime;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PromotionalCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!access()->allow('manage-promo-codes')) return response('', 403);

        if ($request->ajax()) {

            $promotionalCodes = PromotionalCode::
                when(request('promoTypeFilter'), function ($query) {

                    $query->where('promo_type', request('promoTypeFilter'));
                })
                ->when(request('fromDateFilter') || request('untilDateFilter'), function ($query) {

                    $fromDate = request('fromDateFilter') ? (new DateTime(request('fromDateFilter')))->format('Y-m-d H:i:s') : null;
                    $toDate = request('untilDateFilter') ? (new DateTime(request('untilDateFilter')))->format('Y-m-d H:i:s') : null;

                    // Apply the date range filter
                    if ($fromDate && $toDate) {
                        $query->whereBetween('valid_from', [$fromDate, $toDate])
                            ->orWhereBetween('valid_until', [$fromDate, $toDate]);
                    } elseif ($fromDate) {
                        $query->whereDate('valid_from', '>=', $fromDate);
                    } elseif ($toDate) {
                        $query->whereDate('valid_until', '<=', $toDate);
                    }
                })
                ->orderBy('created_at','desc')
                ->get()
                ->map(function ($promotionalCode) {

                    $products = '';
                    if (count($promotionalCode->productVariations) > 0) {

                        $no = 1;
                        foreach ($promotionalCode->productVariations as $product) {

                            $products .= '<span> <b>' . $no++ . '.) </b>' . $product->name . '</span><br>';
                        }
                    }

                    $categories = '';
                    if (count($promotionalCode->productCategories) > 0) {

                        $no = 1;
                        foreach ($promotionalCode->productCategories as $cat) {

                            $categories .= '<span> <b>' . $no++ . '.) </b>' . $cat->title . '</span><br>';
                        }
                    }


                    return (object)[

                        'id' => $promotionalCode->id,
                        'code' => $promotionalCode->code,
                        'promo_type' => strtoupper(str_replace('_', ' ', $promotionalCode->promo_type)),
                        'description' => $promotionalCode->description,
                        'products' => $products,
                        'categories' => $categories,
                        'valid_from' => (new DateTime($promotionalCode->valid_from))->format('d/m/Y H:i:s'),
                        'valid_until' => (new DateTime($promotionalCode->valid_until))->format('d/m/Y H:i:s'),
                        'discount_type' => strtoupper($promotionalCode->discount_type),
                        'discount_value' => number_format($promotionalCode->discount_value, 2),
                        'discount_value_2' => number_format($promotionalCode->discount_value_2, 2),
                        'discount_value_3' => number_format($promotionalCode->discount_value_3, 2),
                        'usage_limit' => number_format($promotionalCode->usage_limit),
                        'reservations_count' => number_format($promotionalCode->reservations_count),
                        'used_count' => number_format($promotionalCode->used_count),
                    ];

                });

            return Datatables::of($promotionalCodes)
                ->addColumn('action', function ($model) {


                    $promoCode = PromotionalCode::find($model->id);
                    $reservations = optional($promoCode->customersReservations)->first() ?? optional($promoCode->thirdPartiesReservations)->first() ?? ($promoCode->referralReservations)->first();


                    $routeShow = route('biller.promotional-codes.show', $model->id);
                    $routeEdit = route('biller.promotional-codes.edit', $model->id);
                    $routeDelete = route('biller.delete-promo-code', $model->id);

                    $viewButton = '<a  target="_blank" href="' . $routeShow . '" class="btn btn-secondary round mr-1">View</a>';
                    $editButton = access()->allow('edit-promo-codes') ? '<a  target="_blank" href="' . $routeEdit . '" class="btn btn-twitter round mr-1">Edit</a>' : '';
                    $deleteButton = access()->allow('delete-promo-codes') ? '<a href="' . $routeDelete . '" class="btn btn-danger round delete mr-1">Delete</a>' : '';

                    return $viewButton . $editButton . ($reservations ? '' : $deleteButton);
                })
                ->addColumn('promo_link', function($model){
                    $secureToken = hash('sha256', $model->id . env('APP_KEY'));
                    $link = route('self_enroll', [
                        'promo_code_id' => $model->id,
                        'token' => $secureToken
                    ]);
                    return $link;
                })
                ->rawColumns(['action', 'products', 'categories'])
                ->make(true);

        }


        return view('focus.promotional_codes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!access()->allow('create-promo-codes')) return response('', 403);

        $productCategories = ProductCategory::whereHas('products')
            ->select('id', 'title')
            ->get()
            ->toArray();
        $currencies = Currency::all();
        $company = Company::find(auth()->user()->ins);

        return view('focus.promotional_codes.create', compact( 'productCategories','currencies','company'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {

        if (!access()->allow('create-promo-codes')) return response('', 403);

        $validated = $request->validate([

            'code' => ['required', 'string', 'regex:/^[A-Z0-9]+$/', 'min:4', 'max:14'],

            'description' => ['required', 'string', 'max:2400'],

            'promo_type' => ['required', 'in:specific_products,product_categories,description_promo'], // Must be either 'percentage' or 'fixed'

            'usage_limit' => ['required', 'integer', 'min:1', // Usage Limit must be a positive integer

                    function ($attribute, $value, $fail) use ($request) {

                        $resTotals = (int) request('res_limit_1') + (int) request('res_limit_2') + (int) request('res_limit_3');

                        if ( $resTotals > (int) $value) {
                            $fail("Reservation Limits total cannot exceed the usage limit.");
                        }
                    },
                ],

            'reservation_period' => ['required', 'integer', 'min:1'], // Usage Limit must be a positive integer

            'discount_type' => ['required', 'in:percentage,fixed'], // Must be either 'percentage' or 'fixed'

            'discount_value' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'discount_value_2' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'discount_value_3' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number

            'res_limit_1' => ['required', 'numeric', 'min:1',

                function ($attribute, $value, $fail) use ($request) {

                    $discount = request('discount_value');
                    if (!empty($discount) && $discount != 0 && empty($value)) {
                        $fail("Reservation Limit 1 is required when Tier 1 Discount Value is not null and not equal to 0.");
                    }
                },

                function (string $attribute, $value, Closure $fail) {

                    $discount = request('discount_value');
                    if ($discount && intval($discount) != 0 && empty(request('res_limit_1'))) {
                        $fail("Reservation Limit 1 is required when Tier 1 Discount Value is not null and not equal to 0.");
                    }
                }
            ],
            'res_limit_2' => ['nullable', 'numeric', 'min:0',

                function ($attribute, $value, $fail) use ($request) {

                    $discount = request('discount_value_2');
                    if ($discount && intval($discount) != 0 && empty(request('res_limit_2'))) {
                        $fail("Reservation Limit 2 is required when Tier 2 Discount Value is not null and not equal to 0.");
                    }
                },
            ],
            'res_limit_3' => ['nullable', 'numeric', 'min:0',

                function ($attribute, $value, $fail) use ($request) {

                    $discount = request('discount_value_3');
                    if ($discount && intval($discount) != 0 && empty(request('res_limit_3'))) {
                        $fail("Reservation Limit 3 is required when Tier 3 Discount Value is not null and not equal to 0.");
                    }
                },
            ],

            'valid_from' => ['nullable', 'date', 'before_or_equal:valid_until'], // Optional, must be a valid date
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'], // Optional, must be a valid date

            'status' => ['required', 'in:0,1'], // Must be '0' (Disabled) or '1' (Active)

            'currency_id' => ['nullable'], 
            'total_commission_type' => ['required', 'in:percentage,fixed'], 
            'total_commission' => ['required', 'numeric', 'min:0'], // Discount Value must be a positive number
            'company_commission' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'commision_type' => ['required', 'in:percentage,fixed'], 
            'cash_back_1' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_2' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_3' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            
            'company_commission_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_back_1_amount' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_2_amount' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_3_amount' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number

            'company_commission_percent' => ['nullable', 'numeric', 'min:0'],
            'cash_back_1_percent' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_2_percent' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_3_percent' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            // Conditional Validation
            'product_categories' => [
                'required_if:promo_option,categories',
                'array',
                'min:1',
            ],
            'product_categories.*' => ['integer', 'exists:product_categories,id'], // Validate each category ID exists in DB

            'products' => [
                'required_if:promo_option,specific',
                'array',
                'min:1',
            ],
            'products.*' => ['integer', 'exists:products,id'], // Validate each product ID exists in DB
            'description_promo' => [
                'required_if:promo_option,description_promo',
            ],
            'flier_path' => 'nullable|max:100024',
            'caption' => ['nullable'],
        ]);


        try {
            DB::beginTransaction();

            $validated['valid_from'] = (new DateTime($validated['valid_from']))->format('Y-m-d H:i:s');
            $validated['valid_until'] = (new DateTime($validated['valid_until']))->format('Y-m-d H:i:s');

            $promotionalCode = PromotionalCode::create($validated);
            $promotionalCode->uuid = Str::uuid()->toString();
            $promotionalCode->save();

            $file = $request->file('flier_path');

            if ($file) {

                $originalName = $file->getClientOriginalName();

                // Generate a unique file name using uniqid
                $uniqueFileName = uniqid(pathinfo(str_replace(' ', '', $originalName), PATHINFO_FILENAME) . '-', true) . '.' . $file->getClientOriginalExtension();

                // Store the file in the public storage (you can choose a different disk if needed)
                $flierPath = $file->storeAs('promo-code-fliers', $uniqueFileName, 'public');

                $promotionalCode->flier_path = $flierPath;
                $promotionalCode->save();
            }

            if (!empty($validated['product_categories'])) {
                // Sync product categories
                $promotionalCode->productCategories()->sync($validated['product_categories']);
            } elseif (!empty($validated['products'])) {
                // Sync specific products
                $promotionalCode->productVariations()->sync($validated['products']);
            }

            DB::commit();

        } catch (Exception $ex) {

            DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.promotional-codes.index'), ['flash_success' => "Promotional Code Created Successfully."]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!access()->allow('manage-promo-codes')) return response('', 403);

        $promotionalCode = PromotionalCode::findOrFail($id);

        $productCategories = ProductCategory::whereHas('products')
            ->select('id', 'title')
            ->get()
            ->toArray();

        $secureToken = hash('sha256', $promotionalCode->id . env('APP_KEY'));
        $promoLink = route('self_enroll', [
            'promo_code_id' => $promotionalCode->id,
            'token' => $secureToken
        ]);
        $commission = $this->formatCommission($promotionalCode,'cash_back_1');
        $discountValue = $promotionalCode->discount_value;
        $discount = $promotionalCode->discount_type == 'fixed' ? "KES {$discountValue}" : "{$discountValue} %";

        $endDate = Carbon::parse($promotionalCode->valid_until)->format('F j, Y \\a\\t g:i A');

        return view('focus.promotional_codes.show', compact('promotionalCode', 'productCategories','promoLink','discount','commission','endDate'));

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
            $percentV = $percentValue;
            return $percentV.'%';
        }

        if ($promoCode->total_commission_type === 'fixed') {
            $fieldName = $field . '_amount';
            return amountFormat($promoCode->$fieldName);
        }

        return 0;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!access()->allow('edit-promo-codes')) return response('', 403);

        $promotionalCode = PromotionalCode::findOrFail($id);

        $productCategories = ProductCategory::whereHas('products')
            ->select('id', 'title')
            ->get()
            ->toArray();
        $currencies = Currency::all();
        $company = Company::find(auth()->user()->ins);

//        return $promotionalCode->productCategories->pluck('id');

//        return $promotionalCode->productVariations->pluck('id');

        return view('focus.promotional_codes.edit', compact('promotionalCode', 'productCategories', 'currencies','company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!access()->allow('edit-promo-codes')) return response('', 403);

        $validated = $request->validate([

            'code' => ['required', 'string', 'regex:/^[A-Z0-9]+$/', 'min:4', 'max:14'],

            'description' => ['required', 'string', 'max:2400'],

            'promo_type' => ['required', 'in:specific_products,product_categories,description_promo'], // Must be either 'percentage' or 'fixed'

            'usage_limit' => ['required', 'integer', 'min:1', // Usage Limit must be a positive integer

                function ($attribute, $value, $fail) use ($request) {

                    $resTotals = (int) request('res_limit_1') + (int) request('res_limit_2') + (int) request('res_limit_3');

                    if ( $resTotals > (int) $value) {
                        $fail("Reservation Limits total cannot exceed the usage limit.");
                    }
                },
            ],

            'reservation_period' => ['required', 'integer', 'min:1'], // Usage Limit must be a positive integer

            'discount_type' => ['required', 'in:percentage,fixed'], // Must be either 'percentage' or 'fixed'

            'discount_value' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'discount_value_2' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'discount_value_3' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number

            'res_limit_1' => ['required', 'numeric', 'min:1',

                function ($attribute, $value, $fail) use ($request) {

                    $discount = request('discount_value');
                    if (!empty($discount) && $discount != 0 && empty($value)) {
                        $fail("Reservation Limit 1 is required when Tier 1 Discount Value is not null and not equal to 0.");
                    }
                },

                function (string $attribute, $value, Closure $fail) {

                    $discount = request('discount_value');
                    if ($discount && intval($discount) != 0 && empty(request('res_limit_1'))) {
                        $fail("Reservation Limit 1 is required when Tier 1 Discount Value is not null and not equal to 0.");
                    }
                }
            ],
            'res_limit_2' => ['nullable', 'numeric', 'min:0',

                function ($attribute, $value, $fail) use ($request) {

                    $discount = request('discount_value_2');
                    if ($discount && intval($discount) != 0 && empty(request('res_limit_2'))) {
                        $fail("Reservation Limit 2 is required when Tier 2 Discount Value is not null and not equal to 0.");
                    }
                },
            ],
            'res_limit_3' => ['nullable', 'numeric', 'min:0',

                function ($attribute, $value, $fail) use ($request) {

                    $discount = request('discount_value_3');
                    if ($discount && intval($discount) != 0 && empty(request('res_limit_3'))) {
                        $fail("Reservation Limit 3 is required when Tier 3 Discount Value is not null and not equal to 0.");
                    }
                },
            ],

            'valid_from' => ['nullable', 'date', 'before_or_equal:valid_until'], // Optional, must be a valid date
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'], // Optional, must be a valid date

            'status' => ['required', 'in:0,1'], // Must be '0' (Disabled) or '1' (Active)

            'currency_id' => ['nullable'], 
            'total_commission_type' => ['required', 'in:percentage,fixed'], 
            'total_commission' => ['required', 'numeric', 'min:0'], // Discount Value must be a positive number
            'company_commission' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'commision_type' => ['required', 'in:percentage,fixed'], 
            'cash_back_1' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_2' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_3' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number

            'company_commission_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_back_1_amount' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_2_amount' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_3_amount' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number

            'company_commission_percent' => ['nullable', 'numeric', 'min:0'],
            'cash_back_1_percent' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_2_percent' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            'cash_back_3_percent' => ['nullable', 'numeric', 'min:0'], // Discount Value must be a positive number
            // Conditional Validation
            'product_categories' => [
                'required_if:promo_option,categories',
                'array',
                'min:1',
            ],
            'product_categories.*' => ['integer', 'exists:product_categories,id'], // Validate each category ID exists in DB

            'products' => [
                'required_if:promo_option,specific',
                'array',
                'min:1',
            ],
            'products.*' => ['integer', 'exists:products,id'], // Validate each product ID exists in DB
            'description_promo' => [
                'required_if:promo_option,description_promo',
            ],
            'flier_path' => 'nullable|max:10024',
            'caption' => ['nullable'],
        ]);


        try {
            DB::beginTransaction();

            $validated['valid_from'] = (new DateTime($validated['valid_from']))->format('Y-m-d H:i:s');
            $validated['valid_until'] = (new DateTime($validated['valid_until']))->format('Y-m-d H:i:s');

            $promotionalCode = PromotionalCode::findOrFail($id);
            $originalStatus = $promotionalCode->status;

            $promotionalCode->update($validated);


            $file = $request->file('flier_path');

            if ($file) {

                $originalName = $file->getClientOriginalName();

                // Generate a unique file name using uniqid
                $uniqueFileName = uniqid(pathinfo(str_replace(' ', '', $originalName), PATHINFO_FILENAME) . '-', true) . '.' . $file->getClientOriginalExtension();

                // Store the file in the public storage (you can choose a different disk if needed)
                $flierPath = $file->storeAs('promo-code-fliers', $uniqueFileName, 'public');

                $promotionalCode->flier_path = $flierPath;
                $promotionalCode->save();
            }

            if (request('remove_flier') == 1){

                $promotionalCode->flier_path = '';
                $promotionalCode->save();
            }



            if (!empty($validated['product_categories'])) {
                // Sync product categories
                $promotionalCode->productCategories()->sync($validated['product_categories']);
                $promotionalCode->productVariations()->sync([]);

            } elseif (!empty($validated['products'])) {
                // Sync specific products
                $promotionalCode->productVariations()->sync($validated['products']);
                $promotionalCode->productCategories()->sync([]);
            }


            if ($originalStatus == 1 && $validated['status'] == 0){

                $customerReferrals = CustomersPromoCodeReservation::where('promo_code_id', $promotionalCode->id)
                    ->where('status', 'reserved')
                    ->get();
                foreach ($customerReferrals as $ref) $ref->update(['status' => 'cancelled']);

                $thirdReferrals = ThirdPartiesPromoCodeReservation::where('promo_code_id', $promotionalCode->id)
                    ->where('status', 'reserved')
                    ->get();
                foreach ($thirdReferrals as $ref) $ref->update(['status' => 'cancelled']);


                $tertiaryReferrals = ReferralsPromoCodeReservation::where('promo_code_id', $promotionalCode->id)
                    ->where('status', 'reserved')
                    ->get();
                foreach ($tertiaryReferrals as $ref) $ref->update(['status' => 'cancelled']);
            }

            DB::commit();

        }
        catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.promotional-codes.index'), ['flash_success' => "Promotional Code Updated Successfully."]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {
        if (!access()->allow('delete-promo-codes')) return response('', 403);

        $promotionalCode = PromotionalCode::findOrFail($id);

        $reservations = optional($promotionalCode->customersReservations)->first() ?? optional($promotionalCode->thirdPartiesReservations)->first() ?? ($promotionalCode->referralReservations)->first();


        if ($reservations) return new RedirectResponse(route('biller.promotional-codes.index'), ['flash_error' => "Action Denied! Promotional Code '" . $promotionalCode->code . "' Has Reservations"]);


        try {
            DB::beginTransaction();

            $promotionalCode->productVariations()->detach();
            $promotionalCode->productCategories()->detach();
            $promotionalCode->delete();

            DB::commit();

        } catch (Exception $ex) {

            DB::rollBack();
            return response()->json([
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ], 500);
        }

        return new RedirectResponse(route('biller.promotional-codes.index'), ['flash_success' => "Promotional Code Deleted Successfully."]);
    }


    public function checkPromoCodeAvailability(Request $request)
    {
        // Validate the incoming promo code
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'regex:/^[A-Z0-9]+$/', // Only uppercase letters and numbers (no spaces)
                'min:4',
                'max:14'
            ]
        ]);

        $promoCode = $request->input('code');

        // Check if the promo code exists in the database
        $exists = PromotionalCode::where('code', $promoCode)
            ->when(request('codeId'), function ($query) {
                return $query->where('id', '!=', request('codeId'));
            })
            ->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Promo code is already taken.' : 'Promo code is available.'
        ]);
    }

    public function getProducts(Request $request)
    {
        try {
            // Get the search term (optional) and selected categories from the request
            $searchTerm = $request->get('search', ''); // Default to empty string if no search term
            $categories = $request->get('categories', []); // Selected categories

            // Determine if the request is for specific products or product categories promotion
            $promoOption = $request->get('promo_option', 'categories'); // Default to 'categories' if not set

            Log::info(json_encode($request->toArray()));

            // Build the query to retrieve products
            $query = ProductVariation::query();

            $query->orderBy('name');

            // If promoOption is 'categories', filter products by categories if selected
            if ($promoOption === 'categories' && !empty($categories)) {
                $query->whereIn('productcategory_id', $categories);
            }

            // If there's a search term, filter products by name
            if (!empty($searchTerm)) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            }

            // Execute the query and get the products
            $products = $query->get(['id', 'name']); // Select only the necessary fields (id and name)

            // Return the products as JSON in the format expected by Select2
            return response()->json($products);

        } catch (Exception $ex) {
            // Log any exception that occurs
            Log::info(
                json_encode([
                    'message' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine(),
                ])
            );
        }
    }



    public function getActiveReservableCodesMetadata($codeId = null)
    {

        return $promoCodes = PromotionalCode::orderBy('created_at', 'desc')
            ->when($codeId, function ($query) use ($codeId) {
                $query->where('id', $codeId);
            })
            ->where('status', 1)
            ->whereRaw('used_count < usage_limit')
            ->get()
            ->map(function ($promotion) {

                $validFrom = (new DateTime($promotion->valid_from))->format('jS M Y, g:iA');
                $validUntil = (new DateTime($promotion->valid_until))->format('jS M Y, g:iA');
                $period = $validFrom . ' to ' . $validUntil;

                $productIds = PromotionalCodeProduct::where('promotional_code_id', $promotion->id)->get()->pluck('product_variation_id');
                $products = ProductVariation::withoutGlobalScopes()->whereIn('id', $productIds)->get();

                $categoryIds = PromotionalCodeProductCategory::where('promotional_code_id', $promotion->id)->get()->pluck('product_category_id');
                $categories = Productcategory::withoutGlobalScopes()->whereIn('id', $categoryIds)->get();

                $items = [];

                if (count($products) > 0) {
                    $items = $products->map(fn($product) => $product->code . ' | ' . $product->name)->toArray(); // Convert to array
                } elseif (count($categories) > 0) {
                    $items = $categories->map(fn($category) => $category->title)->toArray(); // Convert to array
                }

                return (object) [
                    'id' => $promotion->id,
                    'code' => $promotion->code,
                    'redeemable_code' => $promotion->redeemable_code,
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
                    'total_commission' => ucfirst($promotion->total_commission),
                    'total_commission_type' => ucfirst($promotion->total_commission_type),
                    'cash_back_1_amount' => $promotion->cash_back_1_amount,
                    'cash_back_1_percent' => $promotion->cash_back_1_percent,
                    'cash_back_2_amount' => $promotion->cash_back_2_amount,
                    'cash_back_2_percent' => $promotion->cash_back_2_percent,
                    'cash_back_3_amount' => $promotion->cash_back_3_amount,
                    'cash_back_3_percent' => $promotion->cash_back_3_percent,
                    'description_promo' => $promotion->description_promo,
                ];
            });
    }
    public function self_enroll(Request $request)
    {
        $promo_code_id = $request->query('promo_code_id');
        $token = $request->query('token');
        $promo_code = PromotionalCode::withoutGlobalScopes()->where('id',$promo_code_id)->first();
        $expected_token = hash('sha256', $promo_code_id . env('APP_KEY'));
        if ($token !== $expected_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access.'
            ], 403);
        }
        $promoCodes = $this->getActiveReservableCodesMetadata($promo_code_id);
        return view('focus.promotional_code_reservations.create_self_enroll', compact('promoCodes','promo_code_id'));
    }

    public function get_promos(Request $request)
    {
        // join data from multiple tables
        $query = PromotionalCode::query()->withoutGlobalScopes()
            ->where('online_status','published')
            ->where('unique_record',1)
            ->where('valid_until', '>=', Carbon::now())
            ->with(['company']); // assuming relationships exist

        // Filters
        if ($request->has('commission_type')) {
            // dd($request->commission_type);
            $query->where('total_commission_type', $request->commission_type);
        }

        if ($request->has('commission_min') && $request->has('commission_max') && $request->has('commission_type')) {
            if ($request->commission_type === 'fixed') {
                $query->whereBetween('cash_back_1_amount', [$request->commission_min, $request->commission_max]);
            } elseif ($request->commission_type === 'percentage') {
                $query->whereBetween('cash_back_1_percent', [$request->commission_min, $request->commission_max]);
            } else {
                $query->where(function ($q) use ($request) {
                    $q->whereBetween('cash_back_1_amount', [$request->commission_min, $request->commission_max])
                    ->orWhereBetween('cash_back_1_percent', [$request->commission_min, $request->commission_max]);
                });
            }
        }


        if ($request->has('area')) {
            $query->whereHas('company', function ($q) use ($request) {
                // $q->whereJsonContains('location', $request->area);
                $q->where('location', 'like', "%$request->area%");
            });
        }

        if ($request->has('end_date')) {
            $query->whereDate('valid_until', '<=', $request->end_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        $promos = $query->orderBy('published_at','desc')->get()->map(function ($promo) {
            $company = $promo->company;
            $secureToken = hash('sha256', $promo->id . env('APP_KEY'));
            $link = route('self_enroll', [
                'promo_code_id' => $promo->id,
                'token' => $secureToken
            ]);
            $areas = $company->location ? $company->location : '';
            $commission = $this->formatCommission($promo,'cash_back_1');
            $discountValue = $promo->discount_value;
            $discount = $promo->discount_type == 'fixed' ? "KES ".numberFormat($discountValue)."" : "{$discountValue} %";
    

            return [
                'id' => $promo->uuid,
                'area_of_coverage' => $areas,
                'commission' => $commission,
                'discount' => $discount,
                'valid_until' => $promo->valid_until,
                'company_phone' => $company->phone,
                'description' => $promo->description,
                'created_at' => $promo->created_at,
                'updated_at' => $promo->updated_at,
                'published_at' => $promo->published_at,
                'image' => $promo->flier_path 
                ? asset('storage/' . $promo->flier_path) // adjust depending on how images are stored
                : null,
                'end_date' => $promo->valid_until->format('F d, Y \a\t h:i A'),
                'link' => $link,
                'message' => "From: {$company->cname} | Jambo, {$company->cname} is running a special offer on a promotion duped <strong>'{$promo->description}'</strong>, ending on '{$promo->valid_until->format('F d, Y \a\t h:i A')}', <strong>get {$discount} off</strong> your first purchase. Try it out or refer a friend and earn a commission when they purchase. You can contact them directly on {$company->phone}, they are in ".$areas.". Your commission is {$commission}. - <a href='{$link}' target='_blank'>Click and personalize it here</a>"
            ];
        });

        return response()->json($promos);
    }

    public function updateStatus(Request $request, $id)
    {
        $record = PromotionalCode::findOrFail($id);
        $record->online_status = $request->online_status;
        $record->published_at = $request->published_at ? $request->published_at  : '';

        $record->update();

        return back()->with('flash_success', 'Status updated successfully!');
    }
}
