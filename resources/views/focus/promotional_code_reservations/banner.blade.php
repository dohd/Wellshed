<div style="font-family: Arial, sans-serif; margin: 30px auto; display: flex; justify-content: center; align-items: center; background-color: transparent; width: 100%; padding: 20px; box-sizing: border-box;">

    <!-- Banner (Link Preview) -->
    <div style="max-width: 640px; width: 100%; border: 1px solid #ddd; border-radius: 8px; background-color: #ffffff; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="text-decoration: none; color: inherit; display: block;">

            <!-- Header Section -->
            <div style="background-color: #9AC532; color: #ffffff; padding: 16px; text-align: center;">

                <h2 style="margin: 0; font-size: 20px;"> 🌟 Your Trusted Link to Great Service & Rewards</h2>

                @if($payload->referer)
                    <p style="margin: 8px 0 0; font-size: 14px;"><i>Referred by:  <span style="color: white">{{ $payload->referer }}</span></i></p>
                @endif

            </div>

            <!-- Content Section -->
            <div style="padding: 20px;">
                @if ($payload->promo_code->flier_path)

                    <div style="padding: 16px; text-align: center; font-size: 12px; color: #555;">

                        <h4 style="color: #0078D4; text-align: center;"> Offer! Offer! Offer! </h4>


                        @if($payload->promo_code->caption)

                            <br>

                            <p>
                                {{ $payload->promo_code->description }}
                            </p>

                        @endif



                        <div class="fixed-image-container">
                            <img
                                    src="{{ Storage::disk('public')->url($payload->promo_code->flier_path) }}"
                                    alt="Promotional Flier"
                            >
                        </div>

                        <p style="text-align: left; color: red;">
                            To view the whole flier click here 👉
                            <a href="{{ Storage::disk('public')->url($payload->promo_code->flier_path) }}" target="_blank">
                                                    Click
                                                </a>
                        </p>

                    </div>
                    <hr style="margin-top: 16px;">

                @endif


                <p>
                    Dear {{ $payload->customer_company ?? $payload->res->name }},

                    <br><br>

                    Welcome to {{ $payload->company->cname }}! <br><br>

                    Whether you're a new client, a returning one, or you've been referred by a friend - we truly value your interest and trust in us. <br><br>

                    We’re here to offer you great service and real value. <br><br>

                    Let’s connect and see how we can support you!


                    <br><br>

                    📞 Contact: {{ $payload->company->phone }}
                    <br>
                    ✉️ Email: {{ $payload->company->email }}
                </p>

                <hr style="margin-top: 16px;">

                <h4 style="color: #0078D4"> 🎟 * Exclusive promo: {{ $payload->promo_code->code }}! </h4>
                <p style="margin: 0 0 10px; font-size: 16px; color: #333;">
                    ✅ Your Redeemable Promo Code: <span style="font-weight: bold; font-size: 20px; color: #0078D4;">{{ $payload->redeemable_code }}</span>
                </p>

                <br>
                @if ($payload->discount_figure > 0)
                    <h4 style="color: #0078D4">Buy and get {!! $payload->discount_value !!} off!</h4>

                    <p style="margin: 0 0 10px; font-size: 14px; color: #555;">
                        <h4 style="color: #0078D4"> 🎉 Discount Details </h4>
                        {!! $payload->discount !!}
                    </p>
                @endif

                @if($payload->promo_code->description_promo)

                    <p style="margin: 0 0 10px; font-size: 14px; color: #555;">
                        {!! $payload->promo_code->description_promo !!}
                    </p>

                @endif

                @if($payload->products)
                    <p style="margin: 0 0 10px; font-size: 14px; color: #555;">
                        {!! $payload->products !!}
                    </p>
                @elseif($payload->categories)
                    <p style="margin: 0 0 10px; font-size: 14px; color: #555;">
                        <b>Applicable Categories:</b><br>
                        {!! $payload->categories !!}
                    </p>
                @endif

                <p style="margin: 0 0 10px; font-size: 14px; color: #555;">
                    {!! $payload->period !!}
                </p>




                @if(!$forInternal)

                    <div style="padding-top: 26px">


                        @if($isCustomerReservation && $payload->tier == 1)

                            <hr style="margin-top: 16px;">

                            <div>

                                <h4 style="color: #0078D4; padding-top: 26px"> 💙 Happy Customers Corner  </h4>

                                <p>
                                    ⭐ Loved our service? Show your appreciation with a 5-star Rating ⭐
                                </p>

                                <div class="button-wrapper">

                                    @php

                                        $rateUrl = $payload->company->review_url ?? '#';

                                    @endphp

                                    <a href="{{ $rateUrl }}" target="_blank" class="button rate-button">
                                        Click Here to Rate Us!
                                    </a>
                                </div>

                            </div>

                        @endif



                        <div>

                            <hr style="margin-top: 16px;">

                            <h4 style="color: #0078D4; padding-top: 26px"> 📞📥 Contact us to redeem your code  </h4>

                            <div class="button-wrapper">
                                <span> Click here to 👉</span>
                                <a href="{{ route('submit-client-feedback', ['prefix' => $payload->company->id, 'uuid' => $payload->uuid]) }}" target="_blank" class="button contact-button">
                                    Reach out
                                </a>
                            </div>

                        </div>

                        {{--                        @if($payload->tier == 1 || $payload->tier == 2)--}}

                        @if($payload->cb_details && $payload->tier != 3)

                            <hr style="margin-top: 16px;">

                            <div>

                                <h4 style="color: #0078D4; padding-top: 26px"> 🤝 Refer & Earn Rewards  </h4>
                                <h4 style="color: #0078D4; padding-top: 26px">Earn {{ $payload->cash_back }}  for referring us, while your customer gets {!! $payload->discount_value !!} off</h4>
                                <p>
                                    📢 Know someone who needs our products and services? Refer them and get rewarded!
                                </p>
                                <br>

                                {!! $payload->cb_details !!}

                                <br>

                                <div class="button-wrapper">
                                    <span> Click here to 👉</span>
                                    <a href="{{ $payload->url }}" target="_blank" class="button share-button">
                                        Share With Friends
                                    </a>
                                </div>
                                <hr style="margin-top: 16px;">
    
                                <h4 style="color: #0078D4; padding-top: 20px">Copy personalized link to post on your social media platforms</h4>
                                <div class="button-wrapper d-flex align-items-center" style="gap: 10px;">
                                    <input type="hidden" 
                                        class="copy-link form-control d-none" {{-- hidden input --}}
                                        value="{{ $payload->url }}" 
                                        readonly 
                                        style="max-width: 300px; font-size: 1rem;">
                                    
                                    <button class="btn btn-sm btn-primary copy-btn button">
                                        Copy Link
                                    </button>
                                    
                                    <span class="text-success copy-status ms-2" style="display: none;">
                                        Copied!
                                    </span>
                                </div>


                            </div>
                        @else
                            <hr style="margin-top: 16px;">
                            <div>
                                <br>
                                <p>
                                    To refer a friend and earn a commission when they buy, please contact us
                                </p>
                                <br>
                            </div>

                        @endif

                        <div>

                            <hr style="margin-top: 16px;">

                            <div class="title-wrapper">
                                <h4 style="color: #0078D4; margin: 0;">📞📥 Contact us via Whatsapp</h4>
                                <h4 style="color: #0078D4; margin: 0;">🌐 For more information visit our website</h4>
                            </div>

                            <div class="button-row">
                                <div class="button-wrappers">
                                    <span>Click here to 👉</span>
                                    <a href="{{ $payload->company->whatsapp_business_url }}" target="_blank">
                                        <img src="{{ asset('storage/img/company/whatsapp_icon.ico') }}"
                                            style="height: 40px; width: 40px; object-fit: contain; margin-left: 8px;"
                                            alt="WhatsApp Logo">
                                    </a>
                                </div>

                                <div class="button-wrappers">
                                    <span>Click here to 🌐</span>
                                    <a href="{{ $payload->company->website_url }}" target="_blank" class="button share-button">
                                        Website
                                    </a>
                                </div>
                            </div>

                        </div>               


                        @if($isCustomerReservation && $payload->tier == 1)

                            <hr style="margin-top: 16px;">

                            <div>

                                <h4 style="color: #0078D4; padding-top: 26px"> 😡 Disgruntled Customers Corner   </h4>

                                <p>
                                    🔧 Have concerns? Let us make things right!
                                </p>

                                <div class="button-wrapper">
                                    <a href="{{ route('submit-client-feedback', ['prefix' => $payload->company->id, 'uuid' => $payload->uuid]) }}" target="_blank" class="button angry-button">
                                        Submit your feedback
                                    </a>
                                </div>

                            </div>

                        @endif



                    </div>

                @endif

                <hr style="margin-top: 16px;">

                <p style="margin: 0 0 10px; font-size: 14px; color: #555;;">
                    {!! $payload->customer !!}
                </p>


            </div>

           


            <!-- Footer Section -->
            <div style="background-color: #f3f3f3; padding: 16px; text-align: center; font-size: 12px; color: #555;">

                <img src="{{ Storage::disk('public')->url('img/company/theme/' . $payload->company->theme_logo) }}"
                     style="max-height: 220px;  max-width: 100%; object-fit: contain; margin-bottom: 8px;"
                     alt="Company Logo">
                <p style="margin-top: 12px;">{{ $payload->company->cname }} &bull; {{ $payload->company->address }}</p>
                <p>powered by <a href="https://erpproject.co.ke" target="_blank">www.erpproject.co.ke</a></p>
            </div>
        </div>
    </div>
</div>


<style>

    .button-container {
        margin: 60px 0 20px;
        font-size: 16px;
        text-align: center;
        display: flex;
        flex-direction: column; /* Stack buttons vertically */
        align-items: center;
        gap: 15px; /* Add spacing between buttons */
    }

    .button-wrapper {
        width: 100%; /* Ensures full width responsiveness */
        display: flex;
        justify-content: center;
    }

    .button {
        display: inline-block;
        padding: 12px 24px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 16px;
        text-decoration: none;
        text-align: center;
        white-space: nowrap; /* Prevent text wrapping */
    }

    .share-button {
        background-color: #0078D4;
        color: #ffffff;
    }

    .contact-button {
        background-color: #569c0f;
        color: #ffffff;
    }

    .rate-button {
        background-color: gold;
        color: black;
    }

    .angry-button {
        background-color: #F3C000;
        color: #000000;
    }

    @media (min-width: 600px) {
        .button-container {
            flex-direction: row; /* Align buttons horizontally for larger screens */
            gap: 30px; /* Add spacing between buttons */
        }
    }

    .fixed-image-container {
        max-width: 500px;
        max-height: 500px;
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

    .title-wrapper {
        display: flex;
        justify-content: space-between; /* left + right */
        align-items: center;
        margin-top: 26px;
    }

    .button-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }

    .button-wrappers {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .button-wrappers span {
        font-size: 16px;
        font-weight: 500;
        color: #333;
    }

    .button-wrappers a {
        text-decoration: none;
        font-weight: bold;
    }

</style>
<script src="{{ asset('main/js/jquery-3.3.1.min.js') }}"></script>
<script>
    $(document).on('click', '.copy-btn', async function () {
        let $btn = $(this);
        let $input = $btn.siblings('.copy-link');
        let $status = $btn.siblings('.copy-status');
        let textToCopy = $input.val();

        // Modern Clipboard API (preferred)
        if (navigator.clipboard && navigator.clipboard.writeText) {
            try {
                await navigator.clipboard.writeText(textToCopy);
                $status.fadeIn().delay(1000).fadeOut();
            } catch (err) {
                alert('Copy failed. Please copy manually.');
            }
        } else {
            // Fallback: temporary textarea
            let $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(textToCopy).select();
            if (document.execCommand('copy')) {
                $status.fadeIn().delay(1000).fadeOut();
            } else {
                alert('Copy failed. Please copy manually.');
            }
            $temp.remove();
        }
    });
</script>
