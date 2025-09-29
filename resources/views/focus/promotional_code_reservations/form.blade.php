<div class="container row">

    <fieldset class="border p-3 mb-4" style="width: 100%">
        <legend class="w-auto float-none h5">Your Friend / Customer / Agent Contact Details</legend>

        @if (!empty(@$customers))
            <div class="form-row">
                <div class="form-group col-12 col-lg-8">
                    <label for="customer_id">Select Customer</label>
                    <select name="customer_id" id="customer_id" class="form-control select2"
                        data-placeholder="-- Choose a Customer --" required
                        @if (@$reservation) disabled @endif>
                        <option value="" disabled selected></option>
                        @foreach (@$customers as $customer)
                            <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}"
                                data-email="{{ $customer->email }}" @if (@$reservation->customer_id === $customer->id) selected @endif>
                                {{ $customer->company }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <div class="form-row">
                <div class="form-group col-12 col-lg-7">
                    <label for="name">Name</label><span class="text-danger">*</span>
                    <input type="text" id="name" name="name" class="form-control" required
                        @if (@$reservation) value="{{ $reservation->name }}" readonly @endif>

                    @if (!$logged_in_user)
                        <input type="hidden" name="referer_uuid" value="{{ @$resUuid }}">
                        <input type="hidden" name="promo_code_id" value="{{ @$referer->promo_code_id }}">
                    @endif
                </div>
            </div>

            @if (@$isCustomer || !@$isReferral)
                <div class="form-row">
                    <div class="form-group col-12 col-lg-7">
                        <label for="organization">Organization</label>
                        <input type="text" id="organization" name="organization" class="form-control" required
                            @if (@$reservation) value="{{ $reservation->organization }}" readonly @endif>
                    </div>
                </div>
            @endif

            @if (@$reservation)
                <div class="form-row">
                    <div class="form-group col-12 col-lg-8">
                        <label for="customer_id">Link a Customer</label>
                        <select name="customer_id" id="linkCustomer" class="form-control select2"
                            data-placeholder="-- Choose a Customer to Link --"
                            @if (@$isShowing) disabled @endif>
                            <option value="" disabled selected></option>
                            @foreach (@$linkCustomers as $customer)
                                <option value="{{ $customer->id }}" @if (@$reservation->customer_id === $customer->id) selected @endif>
                                    {{ $customer->company }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        @endif

        @if (@$reservation)
            <div class="form-row">
                <div class="form-group col-12 col-lg-7">
                    <label for="Redeemable Code">Redeemable Code</label>
                    <input type="text" id="Redeemable Code" class="form-control"
                        @if (@$reservation) value="{{ $reservation->redeemable_code }}" readonly @endif>
                </div>
            </div>
        @endif

        <div class="form-row">

            <div class="form-group col-12 col-lg-4">
                <label for="phone">Phone (MPESA)</label><span class="text-danger">*</span>
                <input type="text" id="phone" name="phone" class="form-control" required
                    @if (@$reservation) value="{{ $reservation->phone }}" @endif
                    @if (@$isShowing) readonly @endif>
            </div>

            <div class="form-group col-12 col-lg-4">
                <label for="whatsapp_number">Whatsapp Number</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control"
                    @if (@$reservation) value="{{ $reservation->whatsapp_number }}" @endif
                    @if (@$isShowing) readonly @endif>
            </div>

            <div class="form-group col-12 col-lg-4">
                <label for="email">Email (Optional)</label>
                <input type="email" id="email" name="email" class="form-control"
                    @if (@$reservation) value="{{ $reservation->email }}" @endif
                    @if (@$isShowing) readonly @endif>
            </div>
        </div>
    </fieldset>

</div>
<div class="container row">
    <fieldset class="border p-3 mb-4" style="width: 100%">
        <legend class="w-auto float-none h5">Promotional Code Details</legend>

        <!-- Promo Code -->
        <div class="form-group col-12 col-lg-8">
            <label for="promoCode" class="caption">Promo Code</label>
            <select id="promoCode" name="promo_code_id" class="custom-select round select2"
                data-placeholder="Select a Promo Code" @if (@$reservation || !$logged_in_user) disabled @endif>
                <option value="">Select a Promo Code</option>
                @foreach ($promoCodes as $code)
                    <option value="{{ $code->id }}" data-code="{{ $code->code }}"
                        data-type="{{ $code->type }}" data-period="{{ $code->period }}"
                        data-description="{{ $code->description }}" data-items='@json($code->items)'
                        data-discount_type="{{ $code->discount_type }}"
                        data-discount_value="{{ $code->discount_value }}"
                        data-discount_value_2="{{ $code->discount_value_2 }}"
                        data-discount_value_3="{{ $code->discount_value_3 }}"
                        data-commision_type="{{ $code->commision_type }}" data-cash_back_1="{{ $code->cash_back_1 }}"
                        data-cash_back_2="{{ $code->cash_back_2 }}" data-cash_back_3="{{ $code->cash_back_3 }}"
                        data-description_promo="{{ $code->description_promo }}"
                        @if (@$reservation->promo_code_id === $code->id || @$referer->promo_code_id === $code->id) selected @endif> <!-- Properly encode items -->
                        {{ $code->code }}
                    </option>
                @endforeach
            </select>
            <!-- Labels to display the description and items -->
            <label id="codeItems" class="text-muted mt-1"></label>
        </div>
    </fieldset>
</div>

<div class="container row">
    <fieldset class="border p-3 mb-4" style="width: 100%">
        <legend class="w-auto float-none h5">Other Details</legend>
        <div class="form-row">

            <div class="form-group col-12 col-lg-8">
                <label for="customer_id">Tier</label>
                <select name="tier" id="promo_tier" class="form-control" required
                    @if (@$reservation) readonly @endif>
                    @if ($logged_in_user)
                        <option value="1" @if (@$reservation->tier === 1) selected @endif>Tier 1</option>
                    @endif
                    <option value="2" @if (@$reservation->tier === 2) selected @endif>Tier 2</option>
                    <option value="3" @if (@$reservation->tier === 3) selected @endif>Tier 3</option>
                </select>
            </div>

            <div class="form-group col-12 col-lg-8">
                @if (@$cashBack)
                    <label for="cashBack">Cah Back</label>
                    <input type="number" step="0.01" id="cashBack" class="form-control"
                        value="{{ $cashBack }}" readonly>
                @endif
            </div>

        </div>

        <div class="form-row">

            <!-- Message -->
            <div class="form-group col-12 col-lg-8">
                <label for="message">Introductory Message to your Customer / Your Friend / Agent Details</label>
                @php
                    $prefilledMessage = '';
                    $friendName = @$reservation->name ?? 'there';
                    $tenantName = auth()->user()->business->cname ?? 'our company'; // Adjust based on how you're storing tenant info
                    if (!empty(@$customers)) {
                        $prefilledMessage =
                            "Hello [Client's Name],\n\n" .
                            "Thank you for trusting {$tenantName} with your recent purchase – we truly appreciate your support.\n\n" .
                            "We value your feedback! Kindly take a moment to rate us with a 5-star review on Google or share any area where you feel we can improve – your input helps us serve you better.\n\n" .
                            "Additionally, you can refer {$tenantName} to your personal or business friends and earn a commission for every successful referral once they pay. Simply click the link below to refer them.\n\n" .
                            'Once again, thank you for choosing us – we look forward to serving you again.';
                        // $prefilledMessage = "Hey {$friendName},\n\nJust wanted to share about {$tenantName} — they've been great to work with! Whether you check them out yourself or refer a friend, it’s definitely worth it.\n\nYou even earn a commission if your referral makes a purchase.\n\nReach them out if you want more details!";
                    } else {
                        $prefilledMessage = "Jambo, {$tenantName} is running a special offer on a promotion duped '{title}', get {discount} off your first purchase. Try it out or refer a friend and earn a commission when they purchase. You can contact them directly on {company_contact}.";
                    }
                @endphp
                <textarea name="message" id="message" rows="4" class="form-control" placeholder="Enter reservation message"
                    required readonly @if (@$isShowing) readonly @endif
                    @if (@$reservation->message) readonly @endif>{{ @$reservation->message ?? $prefilledMessage }}</textarea>
            </div>

            @if (@$reservation)

                <div class="form-group col-12 col-lg-8">

                    <label for="status" class="caption">Status</label>

                    <select name="status" id="status" class="form-control"
                        @if (@$isShowing) disabled @endif>
                        <option value="reserved" @if (@$reservation->status === 'reserved') selected @endif> Reserved </option>
                        <option value="cancelled" @if (@$reservation->status === 'cancelled') selected @endif> Cancelled </option>

                        @if (@$reservation)
                            <option value="used" @if (@$reservation->status === 'used') selected @endif> Used </option>
                            <option value="expired" @if (@$reservation->status === 'expired') selected @endif> Expired </option>
                        @endif
                    </select>

                </div>

            @endif

        </div>
    </fieldset>
</div>

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                allowClear: true
            });

            // Function to update the description and items
            function updateDescriptionAndItems() {

                const selectedOption = $('#promoCode option:selected');

                // Parse and display the items
                const items = selectedOption.data('items');
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
                    <span>Commision Type: <b>${selectedOption.data('commision_type')}</b></span><br>
                    <span>Referrer on Tier 1: <b>${selectedOption.data('cash_back_1')}</b></span><br>
                    <span>Referrer on Tier 2: <b>${selectedOption.data('cash_back_2')}</b></span><br>
                    <br>


                `);
                        } else {

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
                    <span>Commision Type: <b>${selectedOption.data('commision_type')}</b></span><br>
                    <span>Referrer on Tier 1: <b>${selectedOption.data('cash_back_1')}</b></span><br>
                    <span>Referrer on Tier 2: <b>${selectedOption.data('cash_back_2')}</b></span><br>
                    <br>

                    <h3><u> Promo Details: </u></h3>
                    <p> ${selectedOption.data('description_promo')} </p>
                `);

                        }
                    } catch (e) {
                        console.error("Invalid JSON in data-items:", items, e);
                        $('#codeItems').html('<p class="text-danger">Invalid items format.</p>');
                    }
                } else {
                    $('#codeItems').html('<p>Select a code.</p>');
                }
            }

            // Call the function on page load in case an option is pre-selected
            updateDescriptionAndItems();

            // Update the description and items whenever the selection changes
            $('#promoCode').on('change', updateDescriptionAndItems);

            $('#customer_id').on('change', function() {

                const selected = $('#customer_id option:selected');

                $('#email').val(selected.data('email'));
                $('#phone').val(selected.data('phone'));
                $('#whatsapp_number').val(selected.data('phone'));
            });

            $(document).on('keyup', '#phone', function() {
                let phone = $('#phone').val();
                $('#whatsapp_number').val(phone);
            });

            $('#status').on('input', function() {

                if ($(this).val() === 'cancelled') {
                    let confirmed = confirm(
                        'Are you sure you want to cancel the reservation. \nThis will block its usability if already shared'
                    );

                    if (!confirmed) $(this).val('reserved');
                }
            });

        });
    </script>
@endsection
