<div>
    <div class="card-body">
        <div class="row">
            <div class="form-group col-12 col-lg-6">

                <p id="promoMessage" class="border rounded p-3 bg-white">
                    From: <b>{{ Auth::user()->business->cname }}</b> | 
                    Jambo, {{ Auth::user()->business->cname }} is running a special offer on a promotion duped 
                    '<b>{{ @$promotionalCode->description }}</b>', ending on '<b>{{ $endDate }}</b>',
                    get <b>{{ $discount }}</b> off your first purchase. 
                    Try it out or refer a friend and earn a commission when they purchase. 
                    You can contact them directly on <b>{{ Auth::user()->business->phone }}</b>, 
                    they are in <b>{{ Auth::user()->business->location }}</b>. 
                    Your commission is <b>{{ $commission }}</b>. - Click and personalize it here: 
                    <a href="{{ $promoLink }}" target="_blank">{{ $promoLink }}</a>
                </p>
            </div>
        </div>

        <button id="copyBtn" class="btn btn-success mt-1">
            Copy Message
        </button>
    </div>

    <!-- Promo Code -->

    <div class="row">

        <div class="form-group col-6 col-lg-3">
            <label for="promoCode">Promo Code</label>
            <input type="text" id="promoCode" name="code" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->code}}" @endif
            >
        </div>

    </div>


    <div class="row">

        <div class="form-group col-12 col-lg-6">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="6" class="form-control" readonly>@if(!empty($promotionalCode)){{$promotionalCode->description}}@endif</textarea>
        </div>

    </div>


    <div class="row">

        <div class="form-group col-6 col-lg-3">
            <label for="usageLimit">Usage Count Limit</label>
            <input type="number" step="1" id="usageLimit" name="usage_limit" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->usage_limit}}" @endif
            >
        </div>

        <div class="form-group col-6 col-lg-3">
            <label for="reservationPeriod">Reservation Period (days)</label>
            <input type="number" step="1" id="reservationPeriod" name="reservation_period" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->reservation_period}}" @endif
            >
        </div>

    </div>

    <div class="row mt-3 mb-2">

        <h2 class="col-6">Discounts</h2>
        <hr class="col-7 mt-0">

    </div>


    <div class="row">

        <div class="form-group col-6">
            <label for="discountType">Discount Type</label>
            <select name="discount_type" id="discountType" class="form-control" readonly>

                <option value="percentage"
                        @if(@$promotionalCode->discount_type === 'percentage') selected @endif
                >
                    Percentage e.g. '25% off'
                </option>

                <option value="fixed"
                        @if(@$promotionalCode->discount_type === 'fixed') selected @endif
                >
                    Fixed Amount e.g. 'KES 50 off'
                </option>
            </select>
        </div>

    </div>

    <div class="row">

        <div class="form-group col-4 col-lg-2">
            <label for="discountValue">Discount Value | Tier 1</label>
            <input type="number" step="0.01" id="discountValue" name="discount_value" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->discount_value}}"
                   @else value="0.00"
                   @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="discountValue2">Discount Value  | Tier 2</label>
            <input type="number" step="0.01" id="discountValue2" name="discount_value_2" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->discount_value_2}}"
                   @else value="0.00"
                   @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="discountValue3">Discount Value | Tier 3</label>
            <input type="number" step="0.01" id="discountValue3" name="discount_value_3" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->discount_value_3}}"
                   @else value="0.00"
                   @endif
            >
        </div>

    </div>



    <div class="row">

        <div class="form-group col-4 col-lg-2">
            <label for="resLimit1">Reservation Limit | Tier 1</label>
            <input type="number" step="1" id="resLimit1" name="res_limit_1" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->res_limit_1}}"
                   @else value="0"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="resLimit2">Reservation Limit | Tier 2</label>
            <input type="number" step="1" id="resLimit2" name="res_limit_2" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->res_limit_2}}"
                   @else value="0"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="resLimit3">Reservation Limit | Tier 3</label>
            <input type="number" step="1" id="resLimit3" name="res_limit_3" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->res_limit_3}}"
                   @else value="0"
                    @endif
            >
        </div>

    </div>

    <div class="row mt-3 mb-2">

        <h2 class="col-6">Commission to Referrer</h2>
        <hr class="col-7 mt-0">

    </div>

    <div class="row">

        <div class="form-group col-4 col-lg-2">
            <label for="resLimit1">Commission if Tier 1 sells</label>
            <input type="number" step="0.01" id="cashBack1" name="cash_back_1" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->cash_back_1}}"
                   @else value="0.00"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="cashBack2">Commission if Tier 2 sells</label>
            <input type="number" step="0.01" id="cashBack2" name="cash_back_2" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->cash_back_2}}"
                   @else value="0.00"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-3">
                       <label for="cashBack3">Commission to Tier 1, if Tier 2 sells</label>
                       <input type="number" step="0.01" id="cashBack3" name="cash_back_3" class="form-control" readonly
                              @if(!empty($promotionalCode)) value="{{$promotionalCode->cash_back_3}}"
                              @else value="0.00"
                               @endif
                       >
        </div>

        <hr class="col-7">

    </div>




    <div class="form-group row">

        <div class="form-group col-6 col-lg-3">
            <label for="validFrom" class="control-label">Valid From</label>
            <input type="datetime-local" id="validFrom" name="valid_from" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->valid_from}}" @endif
            >
        </div>

        <div class="form-group col-6 col-lg-3">
            <label for="validUntil" class="control-label">Valid Until</label>
            <input type="datetime-local" id="validUntil" name="valid_until" class="form-control" readonly
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->valid_until}}" @endif
            >
        </div>

    </div>

    <div class="row">

        <div class="form-group col-6">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control" readonly="">

                <option value="0"
                        @if(@$promotionalCode->status === 0) selected @endif
                >
                    Disabled
                </option>

                <option value="1"
                        @if(@$promotionalCode->status === 1) selected @endif
                >
                    Active
                </option>
            </select>
        </div>

    </div>


    <!-- Promotion Type Radio Buttons -->
    <div class="form-group">
        <label>Promotion Type</label><br>

        <div>
            <input type="radio" id="productCategoriesPromo" name="promo_type" value="product_categories" disabled
                   @if(@$promotionalCode->promo_type === 'product_categories') checked @else checked @endif
            >
            <label for="productCategoriesPromo">Product Categories Promotion</label>
        </div>

        <div>
            <input type="radio" id="specificProductsPromo" name="promo_type" value="specific_products" disabled
                   @if(@$promotionalCode->promo_type === 'specific_products') checked @endif
            >
            <label for="specificProductsPromo">Specific Products Promotion</label>
        </div>

        <div>
            <input type="radio" id="descriptionPromo" name="promo_type" value="description_promo" disabled
                   @if(@$promotionalCode->promo_type === 'description_promo') checked @endif
            >
            <label for="descriptionPromo">Custom Descriptive Promotion</label>
        </div>

    </div>

    <!-- Product Categories -->
    <div class="form-group" id="categorySelectWrapper">
        <label for="productCategories">Select Product Categories</label>
        <select id="productCategories" name="product_categories[]" class="form-control select2" multiple readonly>

            @foreach(@$productCategories as $category)
                <option value="{{ $category['id'] }}"
                        @if(!empty($promotionalCode) && in_array($category['id'], $promotionalCode->productCategories->pluck('id')->toArray())) selected @endif>
                    {{ $category['title'] }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Products -->
    <div class="form-group" id="productSelectWrapper" style="display: none;">
        <label for="products">Select Products</label>
        <select id="products" name="products[]" class="form-control select2" multiple readonly>
            <!-- Options will be dynamically loaded via AJAX -->
            @if(!empty($promotionalCode) && count($promotionalCode->productVariations) > 0)
                @foreach($promotionalCode->productVariations as $product)
                    <option value="{{ $product['id'] }}" selected>{{ $product['name'] }}</option>
                @endforeach
            @endif

        </select>
    </div>

    <!-- Description Promo -->
    <div class="form-group row" id="descriptionPromoWrapper" style="display: none;">

        <label for="descriptionPromoText" class="col-12">Describe your promo</label>
        <textarea id="descriptionPromoText" name="description_promo" class="form-control col-8 ml-1" rows="4" readonly>{{ @$promotionalCode->description_promo }}</textarea>

    </div>

    <!-- File Upload -->
    <div class="mb-3 mt-3">


        @if(@$promotionalCode->flier_path)

            <label for="flier_path">Promotion Flier</label>
            <hr>


            <div style="margin-top: 20px; padding: 16px; text-align: center; font-size: 12px; color: #555;">

                <div class="fixed-image-container">
                    <img
                            src="{{ Storage::disk('public')->url(@$promotionalCode->flier_path) }}"
                            alt="Promotional Flier"
                    >
                </div>

            </div>



        @endif
    </div>


</div>

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        $(document).ready(function () {
            $('.select2').select2().on('select2:selecting', function(e) {
                e.preventDefault(); // Prevent adding new items
            }).on('select2:unselecting', function(e) {
                e.preventDefault(); // Prevent removing selected items
            });

            detectPromoType();

            $("#copyBtn").on("click", function () {
                let message = $("#promoMessage").text().trim();

                // Create a temp textarea to copy
                let temp = $("<textarea>");
                $("body").append(temp);
                temp.val(message).select();
                document.execCommand("copy");
                temp.remove();

                alert("Message copied to clipboard!");
            });

            // Handle Promotion Type Radio Button Changes
            $('input[name="promo_type"]').change(function () {
                detectPromoType();
            });

            function detectPromoType() {
                let promoOption = $('input[name="promo_type"]:checked').val();

                if (promoOption === 'product_categories') {
                    $('#categorySelectWrapper').show();
                    $('#productCategories').prop('disabled', false);
                    $('#productSelectWrapper').hide();
                    $('#products').prop('disabled', true).val(null).trigger('change');

                    $('#descriptionPromoText').val('');
                    $('#descriptionPromoWrapper').hide();
                }
                else if (promoOption === 'specific_products') {
                    $('#categorySelectWrapper').hide();
                    $('#productCategories').prop('disabled', true).val(null).trigger('change');
                    $('#productSelectWrapper').show();
                    $('#products').prop('disabled', false);

                    $('#descriptionPromoText').val('');
                    $('#descriptionPromoWrapper').hide();

                    loadProducts($('#productCategories').val());
                }
                else if (promoOption === 'description_promo') {
                    $('#categorySelectWrapper').hide();
                    $('#productCategories').prop('disabled', true).val(null).trigger('change');

                    $('#productSelectWrapper').hide();
                    $('#products').prop('disabled', true).val(null).trigger('change');

                    $('#descriptionPromoWrapper').show();
                }
            }

            function loadProducts(selectedCategories = []) {
                $('#products').select2({
                    ajax: {
                        url: '{{ route("biller.get-promo-products") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                                categories: selectedCategories,
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(function (product) {
                                    return {
                                        id: product.id,
                                        text: product.name,
                                    };
                                }),
                            };
                        },
                        cache: true,
                    },
                });
            }

            $('#productCategories').change(function () {
                let selectedCategories = $(this).val();
                if ($('input[name="promo_type"]:checked').val() === 'specific_products') {
                    loadProducts(selectedCategories);
                }
            });

            // Pre-select values on page load
            let initialCategories = {!! json_encode($selectedCategories ?? []) !!};
            let initialProducts = {!! json_encode($selectedProducts ?? []) !!};

            if (initialCategories.length > 0) {
                $('#productCategories').val(initialCategories).trigger('change');
            }

            if (initialProducts.length > 0) {
                loadProducts(initialCategories);
                setTimeout(() => {
                    $('#products').val(initialProducts).trigger('change');
                }, 500);
            }

            // Promo Code validation
            $('#promoCode').on('input', function () {

                let promoCode = $(this).val().toUpperCase().replace(/\s/g, '');
                $(this).val(promoCode);

                if (/^[A-Z0-9]+$/.test(promoCode) && promoCode.length >= 4 && promoCode.length <= 14) {
                    $.ajax({
                        url: '{{ route("biller.check-promo-code") }}',
                        method: 'POST',
                        data: {
                            code: promoCode,
                            codeId: @json(@$promotionalCode->id),
                            _token: '{{ csrf_token() }}',
                        },
                        success: function (response) {
                            $('#promoCodeFeedback').html(
                                `<span style="color: ${response.available ? 'green' : 'red'};">
                                    ${response.available ? 'Promo code is available!' : 'Promo code is already taken.'}
                                 </span>`
                            );
                        },
                        error: function () {
                            $('#promoCodeFeedback').html('<span style="color: red;">Error validating promo code.</span>');
                        },
                    });
                } else {
                    $('#promoCodeFeedback').html('<span style="color: red;">Invalid promo code format.</span>');
                }
            }).after('<div id="promoCodeFeedback" style="margin-top: 5px;"></div>');

            // Discount Type Validation
            $('#discountType').change(function () {
                const discountType = $(this).val();
                const discountInputs = $('#discountValue, #discountValue2, #discountValue3');

                // Set input attributes based on the discount type
                if (discountType === 'fixed') {
                    discountInputs.attr({ step: '0.01', min: '0', max: '' });
                } else if (discountType === 'percentage') {
                    discountInputs.attr({ step: '1', min: '0', max: '99' });
                }

                // Retain the current values of the inputs
                discountInputs.each(function () {
                    const value = parseFloat($(this).val());
                    const max = discountType === 'percentage' ? 99 : Infinity;

                    // Validate and clear invalid values
                    if (isNaN(value) || value < 0 || value > max) {
                        $(this).val('');
                    }
                });
            }).trigger('change'); // Trigger change on page load to apply rules

            // Validate discount input values during typing
            $('#discountValue, #discountValue2, #discountValue3').on('input', function () {
                const discountType = $('#discountType').val();
                const value = parseFloat($(this).val());
                const max = discountType === 'percentage' ? 99 : Infinity;

                // Clear invalid values
                if (isNaN(value) || value < 0 || value > max) {
                    $(this).val('');
                }
            });

        });
    </script>
@endsection

<style>

    .fixed-image-container {
        max-width: 800px;
        max-height: 600px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 auto; /* Add this to center the container horizontally */
    }

    .fixed-image-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

</style>
