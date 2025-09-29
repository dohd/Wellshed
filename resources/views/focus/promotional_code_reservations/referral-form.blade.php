<div class="container row">


    @if($refererName)
        <p style="margin-bottom: 30px; font-size: 18px;"><i><span style="font-size: 15px;">This recipient will have been referred by:</span> {{ $refererName }}</i></p>

    @endif


    @if(count($refererChain) > 0)


        <div class="mb-3">

            <p>Here's a list of referrers that came before yours, arranged from most recent to earliest: </p>

            @foreach($refererChain as $index => $parent)
                <span>
                    <b>{{ $index + 1 }}.)</b> <i><span style="font-size: 15px;"></span> {{ $parent }}</i>
                </span>
                <br>
            @endforeach

        </div>

    @endif




    @if(!empty(@$customers))

        <!-- Select Customer -->
        <div class="form-group col-12 col-lg-9">
            <label for="customer_id">Select Customer</label>
            <select name="customer_id" id="customer_id" class="form-control select2" data-placeholder="-- Choose a Customer --" required @if(@$reservation) disabled @endif>
                <option value="" disabled selected></option>
                @foreach (@$customers as $customer)
                    <option
                            value="{{ $customer->id }}"
                            data-phone="{{ $customer->phone }}"
                            data-email="{{ $customer->email }}"
                            @if(@$reservation->customer_id === $customer->id) selected @endif
                    >
                        {{ $customer->company }}
                    </option>
                @endforeach
            </select>
        </div>

    @else

        <div class="form-group col-12 col-lg-9">
            <label for="name">Your Friend's Name</label><span class="text-danger">*</span>
            <input type="text" id="name" name="name" class="form-control" required @if(@$reservation) value="{{ $reservation->name }}" readonly @endif>

            @if(!$logged_in_user)
                <input type="hidden" name="referer_uuid" value="{{@$resUuid}}">
                <input type="hidden" name="promo_code_id" value="{{@$referer->promo_code_id}}">
            @endif
        </div>

{{--        <div class="form-group col-12 col-lg-7">--}}
{{--            <label for="organization">Organization</label>--}}
{{--            <input type="text" id="organization" name="organization" class="form-control" required @if(@$reservation) value="{{ $reservation->organization }}" readonly @endif>--}}
{{--        </div>--}}

    @endif

        <div class="row col-12">


            <div class="form-group col-12 col-lg-3">
                <label for="phone">Your Friend's Phone Number</label><span class="text-danger">*</span>
                <input type="text" id="phone" name="phone" class="form-control" required
                       @if(@$reservation) value="{{ $reservation->phone }}" @endif
                       @if(@$isShowing) readonly @endif
                >
            </div>

            <div class="form-group col-12 col-lg-3">
                <label for="whatsapp_number">Whatsapp Number</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control"
                       @if(@$reservation) value="{{ $reservation->whatsapp_number }}" @endif
                       @if(@$isShowing) readonly @endif
                >
            </div>

             <div class="form-group col-12 col-lg-3">
                <label for="email">Your Friend's Email (Optional)</label>
                <input type="email" id="email" name="email" class="form-control" 
                       @if(@$reservation) value="{{ $reservation->email }}" @endif
                       @if(@$isShowing) readonly @endif
                >
            </div>

        </div>


        <!-- Promo Code -->
    <div class="form-group col-12 col-lg-9">
        <label for="promoCode" class="caption">Promo Code</label>
        <select id="promoCode" name="promo_code_id" class="custom-select round select2" data-placeholder="Select a Promo Code" @if(@$reservation || !$logged_in_user) disabled @endif>
            <option value="">Select a Promo Code</option>
            @foreach ($promoCodes as $code)
                <option value="{{ $code->id }}"
                        data-code="{{ $code->code }}"
                        data-type="{{ $code->type }}"
                        data-period="{{ $code->period }}"
                        data-description="{{ $code->description }}"
                        data-items='@json($code->items)'
                        data-discount_type="{{ $code->discount_type }}"
                        data-discount_value="{{ $code->discount_value }}"
                        data-discount_value_2="{{ $code->discount_value_2 }}"
                        data-discount_value_3="{{ $code->discount_value_3 }}"
                        data-total_commission_type="{{ $code->total_commission_type }}" 
                        data-total_commission="{{ $code->total_commission }}" 
                        data-cash_back_1_percent="{{ $code->cash_back_1_percent }}" 
                        data-cash_back_2_percent="{{ $code->cash_back_2_percent }}" 
                        data-cash_back_3_percent="{{ $code->cash_back_3_percent }}" 
                        data-cash_back_1_amount="{{ $code->cash_back_1_amount }}" 
                        data-cash_back_2_amount="{{ $code->cash_back_2_amount }}" 
                        data-cash_back_3_amount="{{ $code->cash_back_3_amount }}" 
                        data-commision_type="{{ $code->commision_type }}"
                        data-cash_back_1="{{ $code->cash_back_1 }}"
                        data-cash_back_2="{{ $code->cash_back_2 }}"
                        @if(@$reservation->promo_code_id === $code->id || @$referer->promo_code_id === $code->id) selected @endif
                > <!-- Properly encode items -->
                    {{ $code->code }}
                </option>
            @endforeach
        </select>
        <!-- Labels to display the description and items -->
        <label id="codeItems" class="text-muted mt-1"></label>
    </div>

    <div class="form-group col-12 col-lg-9">
        <label for="customer_id">Tier</label>
        <select name="tier" id="promo_tier" class="form-control" required @if(@$reservation) disabled @endif>
{{--            @if($logged_in_user)--}}
{{--                <option value="1" @if(@$reservation->tier === 1) selected @endif>Tier 1</option>--}}
{{--            @endif--}}

            @if($tier2Open && $referer->tier == 1)
                <option value="2" @if(@$reservation->tier === 2) selected @endif>Tier 2</option>
            @endif

            @if($tier3Open && $referer->tier == 2)
                <option value="3" @if(@$reservation->tier === 3) selected @endif>Tier 3</option>
            @endif

        </select>
    </div>

    <!-- Message -->
    <div class="form-group col-12 col-lg-9">
        <label for="message">Personal Introduction Message to Your Friend</label>

        @php

            $discount = 0;
            $redeemable_code = $referer->redeemable_code;
            $company = $referer->promoCode->company->cname ?? 'Our Company';
            $company_contact = $referer->promoCode->company->phone ?? '';
            $title = $referer->promoCode ? $referer->promoCode->description : '';
            if ($tier2Open && $referer->tier == 1) {
                if($referer->promoCode){
                    $discountValue = $referer->promoCode->discount_value_2;
                    $code = $referer->promoCode->discount_type == 'fixed' ? "KES{$discountValue}" : "{$discountValue}%";
                    $discount = $code;
                }
            }elseif ($tier3Open && $referer->tier == 2) {
                if($referer->promoCode){
                    $discountValue = $referer->promoCode->discount_value_3;
                    $code = $referer->promoCode->discount_type == 'fixed' ? "KES {$discountValue}" : "{$discountValue} %";
                    $discount = $code;
                }
            }
            $name = explode(' |', $refererName)[0];

            $default = "Jambo, {$company} is running a special offer on a promotion duped '{$title}', get {$discount} off your first purchase. Try it out or refer a friend and earn a commission when they purchase. You can contact them directly on {$company_contact}."
            // $default = "Hello, this is your friend " . $name . ". I came across an interesting product and thought you might find it valuable. There's a genuine offer available, and I believe it’s worth checking out. Feel free to go through the offer and reach out if you’d like to know more."

        @endphp

        <textarea
                name="message"
                id="message"
                rows="4"
                class="form-control"
                placeholder="Enter reservation message"
                required
                readonly
                @if(@$isShowing) readonly @endif

        > {{ @$reservation ? @$reservation->message : $default }} </textarea>

    </div>

    @if(@$reservation)

        <div class="form-group col-12 col-lg-9">

            <label for="status" class="caption">Status</label>

            <select name="status" id="status" class="form-control" @if(@$isShowing) disabled @endif>
                <option value="reserved" @if(@$reservation->status === 'reserved') selected @endif> Reserved </option>
                <option value="cancelled" @if(@$reservation->status === 'cancelled') selected @endif> Cancelled </option>
            </select>

        </div>

    @endif

</div>

@section('extra-scripts')

    {{ Html::script('focus/js/select2.min.js') }}
    <script>

        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({ allowClear: true });

            // Function to update the description and items
            function updateDescriptionAndItems() {

                const selectedOption = $('#promoCode option:selected');

                // Parse and display the items
                const items = selectedOption.data('items');
                let total_commission_type = selectedOption.data('total_commission_type');
                let tier_1_commission = 0;
                let tier_2_commission = 0;
                if(total_commission_type == 'Percentage'){
                    tier_1_commission = selectedOption.data('cash_back_1_percent');
                    tier_2_commission = selectedOption.data('cash_back_2_percent');
                }else{
                    tier_1_commission = selectedOption.data('cash_back_1_amount');
                    tier_2_commission = selectedOption.data('cash_back_2_amount');

                }
                if (items) {
                    try {

                        if (items.length > 0) {

                            const formattedItems = items.map(item => `<li>- ${item}</li>`).join('');
                            $('#codeItems').html(`
                                <h2> ${selectedOption.data('code')} </h2>
                                <span>Promo type: <b>${selectedOption.data('type')}</b></span><br>
                                <span>Period: <b>${selectedOption.data('period')}</b></span><br>
                                <br>

                                <h3><u>Description:</u></h3>
                                <p> ${selectedOption.data('description')} </p>

                                <h3> <u> Items on Offer </u> </h3>
                                <ul>${formattedItems}</ul>


                                <h3><u>Discounts offered on purchase</u></h3>
                                <span>Discount Type: <b>${selectedOption.data('discount_type')}</b></span><br>
                                <span>Tier 1: <b>${selectedOption.data('discount_value')}</b></span><br>
                                <span>Tier 2: <b>${selectedOption.data('discount_value_2')}</b></span><br>
                                <span>Tier 3: <b>${selectedOption.data('discount_value_3')}</b></span><br>
                                <br>

                                <h3><u>Rewards offered to referrers</u></h3>
                                <span>Commision Type: <b>${total_commission_type}</b></span><br>
                                <span>Referrer on Tier 1: <b>${tier_1_commission}</b></span><br>
                                <span>Referrer on Tier 2: <b>${tier_2_commission}</b></span><br>
                                <br>
                            `);

                        }
                        else {

                            // $('#codeItems').html('<p>No items available.</p>');

                            $('#codeItems').html(`
                                <h2> ${selectedOption.data('code')} </h2>
                                <span>Type: <b>${selectedOption.data('type')}</b></span><br>
                                <span>Period: <b>${selectedOption.data('period')}</b></span><br>
                                <br>

                                <h3><u>Description:</u></h3>
                                <p> ${selectedOption.data('description')} </p>

                                <br>

                                <h3><u>Discounts offered on purchase</u></h3>
                                <span>Discount Type: <b>${selectedOption.data('discount_type')}</b></span><br>
                                <span>Tier 1: <b>${selectedOption.data('discount_value')}</b></span><br>
                                <span>Tier 2: <b>${selectedOption.data('discount_value_2')}</b></span><br>
                                <span>Tier 3: <b>${selectedOption.data('discount_value_3')}</b></span><br>
                                <br>

                                <h3><u>Rewards offered to referrers</u></h3>
                                <span>Commision Type: <b>${total_commission_type}</b></span><br>
                                <span>Referrer on Tier 1: <b>${tier_1_commission}</b></span><br>
                                <span>Referrer on Tier 2: <b>${tier_2_commission}</b></span><br>
                                <br>
                                <h3><u> Promo Details: </u></h3>
                                <p> ${selectedOption.data('description_promo')} </p>
                            `);

                        }
                    }

                    catch (e) {

                        console.error("Invalid JSON in data-items:", items, e);
                        $('#codeItems').html('<p class="text-danger">Invalid items format.</p>');
                    }
                }
                else {

                    $('#codeItems').html('<p>Select a code.</p>');
                }
            }

            // Call the function on page load in case an option is pre-selected
            updateDescriptionAndItems();

            // Update the description and items whenever the selection changes
            $('#promoCode').on('change', updateDescriptionAndItems);

            $('#customer_id').on('change', function (){

               const selected = $('#customer_id option:selected');

               $('#email').val(selected.data('email'));
               $('#phone').val(selected.data('phone'));
            });

            $(document).on('keyup','#phone',function(){
                let phone = $('#phone').val();
                $('#whatsapp_number').val(phone);
            });


            $('#status').on('input', function (){

                if ($(this).val() === 'cancelled') {
                    let confirmed = confirm('Are you sure you want to cancel the reservation. \nThis will block its usability if already shared');

                    if (!confirmed) $(this).val('reserved');
                }
            });

        });

    </script>
@endsection
