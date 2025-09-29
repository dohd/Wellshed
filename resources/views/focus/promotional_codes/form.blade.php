<div>

    <!-- Promo Code -->

    <div class="row">

        <div class="form-group col-6 col-lg-3">
            <label for="promoCode">Promo Code</label>
            <input type="text" id="promoCode" name="code" class="form-control" required
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->code}}" @endif
            >
        </div>
        <div class="form-group col-6 col-lg-3">
            <label for="description">Title</label>
            <input id="description" name="description" value="@if(!empty($promotionalCode)){{$promotionalCode->description}}@endif" class="form-control" required />
        </div>

    </div>


    {{-- <div class="row">

        <div class="form-group col-12 col-lg-6">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" class="form-control" required>@if(!empty($promotionalCode)){{$promotionalCode->description}}@endif</textarea>
        </div>

    </div> --}}


    <div class="row">

        <div class="form-group col-6 col-lg-3">
            <label for="validFrom" class="control-label">Valid From</label>
            <input type="datetime-local" id="validFrom" name="valid_from" class="form-control"
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->valid_from}}" @endif
            >
        </div>

        <div class="form-group col-6 col-lg-3">
            <label for="validUntil" class="control-label">Valid Until</label>
            <input type="datetime-local" id="validUntil" name="valid_until" class="form-control"
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->valid_until}}" @endif
            >
        </div>

    </div>


    <div class="row">

        <div class="form-group col-6">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control">

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


    <div class="row">

        <div class="form-group col-6 col-lg-3">
            <label for="usageLimit">Usage Count Limit</label>
            <input type="number" step="1" id="usageLimit" name="usage_limit" class="form-control" required
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->usage_limit}}" @endif
            >
        </div>

    </div>


    <div class="row d-none">
        <div class="form-group col-6 col-lg-3">
            <label for="reservationPeriod">Reservation Period (days)</label>
            <input type="number" value="1" step="1" id="reservationPeriod" name="reservation_period" class="form-control" required
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->reservation_period}}" @endif
            >
        </div>

    </div>

    <div class="row">

        <div class="form-group col-4 col-lg-2">
            <label for="resLimit1">Reservation Limit | Tier 1</label>
            <input type="number" step="1" id="resLimit1" name="res_limit_1" class="form-control"
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->res_limit_1}}"
                   @else value="0"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="resLimit2">Reservation Limit | Tier 2</label>
            <input type="number" step="1" id="resLimit2" name="res_limit_2" class="form-control"
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->res_limit_2}}"
                   @else value="0"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="resLimit3">Reservation Limit | Tier 3</label>
            <input type="number" step="1" id="resLimit3" name="res_limit_3" class="form-control"
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->res_limit_3}}"
                   @else value="0"
                    @endif
            >
        </div>

    </div>






    <div class="row mt-3 mb-2">

        <h2 class="col-6">Items on Offer</h2>
        <hr class="col-7 mt-0">

    </div>

    <!-- Promotion Type Radio Buttons -->
    <div class="form-group">
        <label>Promotion Type</label><br>

        <div>
            <input type="radio" id="productCategoriesPromo" name="promo_type" value="product_categories"
                   @if(@$promotionalCode->promo_type === 'product_categories') checked @else checked @endif
            >
            <label for="productCategoriesPromo">Product Categories Promotion</label>
        </div>

        <div>
            <input type="radio" id="specificProductsPromo" name="promo_type" value="specific_products"
                   @if(@$promotionalCode->promo_type === 'specific_products') checked @endif
            >
            <label for="specificProductsPromo">Specific Products Promotion</label>
        </div>

        <div>
            <input type="radio" id="descriptionPromo" name="promo_type" value="description_promo"
                   @if(@$promotionalCode->promo_type === 'description_promo') checked @endif
            >
            <label for="descriptionPromo">Custom Descriptive Promotion</label>
        </div>

    </div>

    <!-- Product Categories -->
    <div class="form-group" id="categorySelectWrapper">
        <label for="productCategories">Select Product Categories</label>
        <select id="productCategories" name="product_categories[]" class="form-control select2" multiple>

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
        <select id="products" name="products[]" class="form-control select2" multiple>
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
        <textarea id="descriptionPromoText" name="description_promo" class="form-control col-8 ml-1 tinyinput" rows="4">{{ @$promotionalCode->description_promo }}</textarea>

    </div>



    <div class="row mt-3 mb-2">

        <h2 class="col-6">Discount to Purchaser</h2>
        <hr class="col-7 mt-0">

    </div>

    <div class="row">

        <div class="form-group col-6">
            <label for="discountType">Discount Type</label>
            <select name="discount_type" id="discountType" class="form-control">

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
            <input type="number" step="0.01" id="discountValue" name="discount_value" class="form-control" required
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->discount_value}}"
                   @else value="0.00"
                   @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="discountValue2">Discount Value  | Tier 2</label>
            <input type="number" step="0.01" id="discountValue2" name="discount_value_2" class="form-control" required
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->discount_value_2}}"
                   @else value="0.00"
                   @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="discountValue3">Discount Value | Tier 3</label>
            <input type="number" step="0.01" id="discountValue3" name="discount_value_3" class="form-control" required
                   @if(!empty($promotionalCode)) value="{{$promotionalCode->discount_value_3}}"
                   @else value="0.00"
                   @endif
            >
        </div>

    </div>


    <div class="row mt-3 mb-2">

        <h2 class="col-6">Commission to Referrer / Agent</h2>
        <hr class="col-7 mt-0">

    </div>
    <div class="row">
        <div class="form-group col-4 col-lg-4">
            <label for="total_commission_type">Select Product Commission </label>
            <select name="total_commission_type" id="total_commission_type" class="form-control">

                <option value="percentage"
                        @if(@$promotionalCode->total_commission_type === 'percentage') selected @endif
                >
                    Percentage e.g. '25%'
                </option>

                <option value="fixed"
                        @if(@$promotionalCode->total_commission_type === 'fixed') selected @endif
                >
                    Fixed Amount e.g. 'KES 50'
                </option>
            </select>
        </div>
        <div class="form-group col-4 col-lg-4">
            <label for="total_commission">Total Commission</label>
            <input type="number" value="{{@$promotionalCode->total_commission}}" step="0.01" name="total_commission" id="total_commission" placeholder="0.00" class="form-control" required>
        </div>
    </div>

    <div class="row d-none">

        <div class="form-group col-3">
            <label for="commision_type">Commission Type</label>
            <select name="commision_type" id="commision_type" class="form-control">

                <option value="percentage"
                        @if(@$promotionalCode->commision_type === 'percentage') selected @endif
                >
                    Percentage e.g. '25%'
                </option>

                <option value="fixed"
                        @if(@$promotionalCode->commision_type === 'fixed') selected @endif
                >
                    Fixed Amount e.g. 'KES 50'
                </option>
            </select>
        </div>
        <div class="form-group div_currency d-none col-3">
            <label for="currency">Currency</label>
            <select name="currency_id" id="currency_id" class="form-control">
                <option value="">--select currency--</option>
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->id }}" {{ @$promotionalCode->currency_id == $currency->id ? 'selected' : '' }}>{{ $currency->code }}</option>
                @endforeach
            </select>
        </div>

    </div>

    <h4 class="col-6"><strong>Fixed Percentage Commission Sharing</strong></h4>
    <hr>
    <div class="row">

        <div class="form-group col-4 col-lg-2">
            <label for="company_commission">Tier Zero Commission</label>
            <input type="number" step="0.01" id="company_commission" name="company_commission" class="form-control" readonly
                   @if(empty(@$promotionalCode)) value="{{@$promotionalCode->company_commission ??  $company->company_commission}}"
                   @else value="{{ $company->company_commission }}"
                    @endif
            >
        </div>
        <div class="form-group col-4 col-lg-2">
            <label for="resLimit1">Commission if Tier 1 sells</label>
            <input type="number" step="0.01" id="cashBack1" name="cash_back_1" class="form-control" readonly
                   @if(empty(@$promotionalCode)) value="{{@$promotionalCode->cash_back_1 ?? $company->commission_1 }}"
                   @else value="{{ $company->commission_1 }}"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="cashBack2">Commission if Tier 2 sells</label>
            <input type="number" step="0.01" id="cashBack2" name="cash_back_2" class="form-control" readonly
                   @if(empty(@$promotionalCode)) value="{{@$promotionalCode->cash_back_2 ?? $company->commission_2 }}"
                   @else value="{{ $company->commission_2 }}"
                    @endif
            >
        </div>

        <div class="form-group col-4 col-lg-3">
           <label for="cashBack3">Commission to Tier 1, if Tier 2 sells</label> 
           <input type="number" step="0.01" id="cashBack3" name="cash_back_3" class="form-control" readonly
                  @if(empty(@$promotionalCode)) value="{{@$promotionalCode->cash_back_3 ?? $company->commission_3 }}"
                  @else value="{{ $company->commission_3 }}"
                   @endif
           >
        </div>


    </div>
    <h4 class="col-6"><strong>Apprehended Percentage Total Commission Allocation</strong></h4>
    <hr>
    <div class="row">

        <div class="form-group col-4 col-lg-2">
            <label for="company_commission_percent">Tier Zero Commission (%)</label>
            <input type="number" step="0.01" value="{{ @$promotionalCode->company_commission_percent }}" id="company_commission_percent" name="company_commission_percent" class="form-control" readonly
            >
        </div>
        <div class="form-group col-4 col-lg-2">
            <label for="cash_back_1_percent">Commission if Tier 1 sells (%)</label>
            <input type="number" step="0.01" value="{{ @$promotionalCode->cash_back_1_percent }}" name="cash_back_1_percent" id="cash_back_1_percent" class="form-control" readonly
                   
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="cash_back_2_percent">Commission if Tier 2 sells (%)</label>
            <input type="number" step="0.01" value="{{ @$promotionalCode->cash_back_2_percent }}" name="cash_back_2_percent" id="cash_back_2_percent"  class="form-control" readonly
                  
            >
        </div>

        <div class="form-group col-4 col-lg-3">
           <label for="cash_back_3_percent">Commission to Tier 1, if Tier 2 sells (%)</label> 
           <input type="number" value="{{ @$promotionalCode->cash_back_3_percent }}" step="0.01" name="cash_back_3_percent" id="cash_back_3_percent" class="form-control" readonly
                 
           >
        </div>


    </div>
    <h4 class="col-6"><strong>Apprehended Amount Total Commission Allocation</strong></h4>
    <hr>
    <div class="row">

        <div class="form-group col-4 col-lg-2">
            <label for="company_com">Tier Zero Commission (Amt)</label>
            <input type="number" step="0.01" value="{{ @$promotionalCode->company_commission_amount }}" id="company_com" name="company_commission_amount" class="form-control" readonly
            >
        </div>
        <div class="form-group col-4 col-lg-2">
            <label for="cash_back_1_commission">Commission if Tier 1 sells (Amt)</label>
            <input type="number" step="0.01" value="{{ @$promotionalCode->cash_back_1_amount }}" name="cash_back_1_amount" id="cash_back_1_commission" class="form-control" readonly
                   
            >
        </div>

        <div class="form-group col-4 col-lg-2">
            <label for="cash_back_2_commission">Commission if Tier 2 sells (Amt)</label>
            <input type="number" step="0.01" value="{{ @$promotionalCode->cash_back_2_amount }}" name="cash_back_2_amount" id="cash_back_2_commission"  class="form-control" readonly
                  
            >
        </div>

        <div class="form-group col-4 col-lg-3">
           <label for="cash_back_3_commission">Commission to Tier 1, if Tier 2 sells (Amt)</label> 
           <input type="number" value="{{ @$promotionalCode->cash_back_3_amount }}" step="0.01" name="cash_back_3_amount" id="cash_back_3_commission" class="form-control" readonly
                 
           >
        </div>


    </div>



    <div class="row mt-3 mb-2">

        <h2 class="col-6">Promotion Flier</h2>
        <hr class="col-7 mt-0">

    </div>


    <!-- File Upload -->
    <div class="mb-3">

        <label for="caption">Flier Caption</label>
        <textarea id="caption" name="caption" class="form-control col-8 mb-1" rows="1">{{ @$promotionalCode->caption }}</textarea>

        <label for="flier_path">Promotion Flier</label>
        <input type="file" name="flier_path" id="flier_path" class="form-control col-8">



    @if(@$promotionalCode && @$promotionalCode->flier_path)

            <label for="removeFlier">Remove Current Promotion Flier</label>
            <input type="checkbox" name="remove_flier" id="removeFlier" value="1">


            <hr>

            <div style="margin-top: 20px; padding: 16px; text-align: center; font-size: 12px; color: #555;">

                <div class="fixed-image-container" id="flierContainer">
                    <img
                            src="{{ Storage::disk('public')->url(@$promotionalCode->flier_path) }}"
                            alt="Promotional Flier"
                    >
                </div>

            </div>

        @endif
    </div>



</div>
@include('tinymce.scripts')
@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
            $(document).ready(function () {
                tinymce.init({
                selector: '.tinyinput',
                menubar: 'file edit view format table tools',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                height: 300,
            });
            $('.select2').select2();

            detectPromoType();
            commisionChange();

            // Handle Promotion Type Radio Button Changes
            $('input[name="promo_type"]').change(function () {
                detectPromoType();
            });

            $('#removeFlier').change(function () {

                if ($(this).is(':checked')) $('#flierContainer').slideUp();
                else $('#flierContainer').slideDown();

            });

            $(document).ready(function () {
                function enforceCommissionLimit() {
                    const type = $('#total_commission_type').val();
                    const commissionType = $('#commision_type').val();
                    const commissionInput = $('#total_commission');
                    let value = parseFloat(commissionInput.val()) || 0;

                    const companyCom = parseFloat($('#company_commission').val()) || 0;
                    const cashBack1 = parseFloat($('#cashBack1').val()) || 0;
                    const cashBack2 = parseFloat($('#cashBack2').val()) || 0;
                    const cashBack3 = parseFloat($('#cashBack3').val()) || 0;

                    const clearCommissions = () => {
                        $('#company_com, #cash_back_1_commission, #cash_back_2_commission, #cash_back_3_commission').val(0);
                    };
                    const clearPercentCommissions = () => {
                        $('#company_commission_percent, #cash_back_1_percent, #cash_back_2_percent, #cash_back_3_percent').val(0);
                    };

                    if (type === 'percentage' && value > 100) {
                        commissionInput.val(100);
                        value = 100;
                        clearCommissions();
                        clearPercentCommissions();
                    } 
                    else if (type === 'fixed' && commissionType === 'percentage') {
                        const calcCommission = (percent, base) => (percent / 100) * base;

                        clearPercentCommissions();
                        $('#company_com').val(calcCommission(companyCom, value).toFixed(2));
                        $('#cash_back_1_commission').val(calcCommission(cashBack1, value).toFixed(2));
                        $('#cash_back_2_commission').val(calcCommission(cashBack2, value).toFixed(2));
                        $('#cash_back_3_commission').val(calcCommission(cashBack3, value).toFixed(2));
                    } 
                    else if (type === 'percentage' && commissionType === 'percentage') {
                        const calcCommission = (percent, base) => (percent / 100) * base;

                        clearCommissions();
                        $('#company_commission_percent').val(calcCommission(companyCom, value).toFixed(2));
                        $('#cash_back_1_percent').val(calcCommission(cashBack1, value).toFixed(2));
                        $('#cash_back_2_percent').val(calcCommission(cashBack2, value).toFixed(2));
                        $('#cash_back_3_percent').val(calcCommission(cashBack3, value).toFixed(2));
                    } 
                    else {
                        clearCommissions();
                    }
                }



                // Trigger on change of type
                $('#total_commission_type').on('change', function () {
                    enforceCommissionLimit();
                });

                // Trigger on typing or changing commission value
                $('#total_commission').on('input change', function () {
                    enforceCommissionLimit();
                });
            });

            $('#discountValue').on('keyup', function(){
                let discountValue = $(this).val();
                $('#discountValue2').val(discountValue)
                $('#discountValue3').val(discountValue)
            });


            $('#commision_type').change(function(){
                let commision_type = $(this).val();
                if(commision_type == 'fixed'){
                    $('.div_currency').removeClass('d-none')
                }else{
                    $('.div_currency').addClass('d-none')
                    $('#currency_id').val('');
                }
                $('#total_commission').trigger('change');
            });

            function commisionChange(){
                let commission_type = @json(@$promotionalCode->commision_type);
                if(commission_type == 'fixed'){
                    $('.div_currency').removeClass('d-none')
                }else{
                    $('.div_currency').addClass('d-none')
                    $('#currency_id').val('');
                }
            }

             function splitFixedRules(amount) {
                const ratio = [1, 2, 3];
                const totalParts = ratio.reduce((a, b) => a + b, 0);

                let rawParts = ratio.map(part => (amount * part) / totalParts);
                let intParts = rawParts.map(Math.floor);
                let sum = intParts.reduce((a, b) => a + b, 0);
                let remainder = Math.round(amount - sum);

                // Distribute remainder to lowest priority parts (start from index 0)
                let i = 0;
                while (remainder > 0) {
                    intParts[i]++;
                    remainder--;
                    i++;
                    if (i >= intParts.length) i = 0;
                }

                return intParts;
            }


            $('#usageLimit').change(function() {
                let usage_limit = parseInt(this.value);
                if (isNaN(usage_limit) || usage_limit < 0) return;

                let result = splitFixedRules(usage_limit);
                $('#resLimit1').val(result[0]);
                $('#resLimit2').val(result[1]);
                $('#resLimit3').val(result[2]);
            });


                function detectPromoType() {
                let promoOption = $('input[name="promo_type"]:checked').val();

                if (promoOption === 'product_categories') {
                    $('#categorySelectWrapper').slideDown();;
                    $('#productCategories').prop('disabled', false);
                    $('#productSelectWrapper').slideUp();
                    $('#products').prop('disabled', true).val(null).trigger('change');

                    $('#descriptionPromoText').val('');
                    $('#descriptionPromoWrapper').slideUp();
                }
                else if (promoOption === 'specific_products') {
                    $('#categorySelectWrapper').slideUp();
                    $('#productCategories').prop('disabled', true).val(null).trigger('change');
                    $('#productSelectWrapper').slideDown();;
                    $('#products').prop('disabled', false);

                    $('#descriptionPromoText').val('');
                    $('#descriptionPromoWrapper').slideUp();

                    loadProducts($('#productCategories').val());
                }
                else if (promoOption === 'description_promo') {
                    $('#categorySelectWrapper').slideUp();
                    $('#productCategories').prop('disabled', true).val(null).trigger('change');

                    $('#productSelectWrapper').slideUp();
                    $('#products').prop('disabled', true).val(null).trigger('change');

                    $('#descriptionPromoWrapper').slideDown();;
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
            // $('#discountValue, #discountValue2, #discountValue3').on('input', function () {
            //
            //     const discountType = $('#discountType').val();
            //     const value = parseFloat($(this).val());
            //     const max = discountType === 'percentage' ? 99 : Infinity;
            //
            //     if (($('#discountValue3').val() > $('#discountValue2').val()) || ($('#discountValue2').val() > $('#discountValue').val())){
            //
            //         alert('The values of discounts must be of decreasing value with increase of tiers or of equal value. \nRule: Tier 1 >= Tier 2 >= Tier3');
            //         $(this).val('');
            //     }
            //
            //     // Clear invalid values
            //     if (isNaN(value) || value < 0 || value > max) {
            //         $(this).val('');
            //     }
            // });

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
