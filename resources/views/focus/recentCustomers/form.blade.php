<div class="card-content">
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                   aria-selected="true" style="font-size: 20px;">Email</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                   aria-selected="false" style="font-size: 20px;">Sms</a>
            </li>


        </ul>
      
        <div class="tab-content px-1 pt-1">


              <!---Email tab-->
            <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                {{ Form::open(['route' => $isProspect ? 'biller.email-prospect' : ['biller.email-recent-customer', optional($customer)['id']], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'create-stakeholder']) }}

                @php
                    $sender = \Illuminate\Support\Facades\Auth::user();

                    if (!$isProspect){

                        $templates = [
                            [
                                'subject' => 'Service Follow-Up Inquiry',
                                'content' => '
                                    <p>
                                    Dear ' . optional($customer)['name'] . ',
                                    <br><br>
                                    I hope this message finds you well. I’m reaching out to follow up on the recent services
                                    provided under <strong>' . optional($customer)['last_invoice_title'] . '</strong>, invoiced on
                                    <strong>' . optional($customer)['last_invoice_date'] . '</strong>.
                                    <br><br>
                                    We value your business and are committed to ensuring your complete satisfaction. Should
                                    you require any additional assistance or support, please don’t hesitate to let us know.
                                    <br><br>
                                    Feel free to reach out at your earliest convenience.
                                    <br><br>
                                    Best regards,<br>'
                                    . $sender->fullname . '.<br>'
                                    . $sender->business->cname
                                    . '</p>
                                    ',
                            ],
                            [
                                'subject' => 'New Opportunities for Collaboration',
                                'content' => '
                                    <p>
                                    Dear ' . optional($customer)['name'] . ',
                                    <br><br>
                                    Thank you for your trust in our services, as reflected in the recent project titled
                                    <strong>' . optional($customer)['last_invoice_title'] . '</strong>, invoiced on <strong>'
                                    . optional($customer)['last_invoice_date'] . '</strong>.
                                    <br><br>
                                    As a valued client, we want to ensure that we’re always aligned with your evolving needs.
                                    Are there any upcoming initiatives or requirements where our expertise could support
                                    your goals?
                                    <br><br>
                                    Looking forward to hearing from you.
                                    <br><br>
                                    Warm regards,<br>'
                                    . $sender->fullname . '<br>'
                                    . $sender->business->cname
                                    . '</p>
                                    ',
                            ],
                            [
                                'subject' => 'Special Offers & Continued Support',
                                'content' => '
                                    <p>
                                    Dear ' . optional($customer)['name'] . ',
                                    <br><br>
                                    It has been a pleasure serving you, most recently with the project titled <strong>'
                                    . optional($customer)['last_invoice_title'] . '</strong>, invoiced on <strong>'
                                    . optional($customer)['last_invoice_date'] . '</strong>.
                                    <br><br>
                                    To express our gratitude for your business, we’ll be excited to share exclusive offers
                                    tailored for our esteemed clients.
                                    <br><br>
                                    Let us know how we can best assist you.
                                    <br><br>
                                    Thank you for choosing us as your trusted partner.
                                    <br><br>
                                    Kind regards,<br>'
                                    . $sender->fullname . '<br>'
                                    . $sender->business->cname
                                    . '</p>
                                ',
                            ]
                        ];
                    }

                else $templates = null;
                @endphp

                <div class="row">

                    @if($isProspect)
                        <div class="col-12 col-lg-8 mb-1">
                            <label for="email_address">Prospect Name</label>
                            <input type="text" name="prospect_name" id="prospect_name" class="form-control" required>
                        </div>
                    @endif

                    <div class="col-12 col-lg-8">
                        <label for="email_address">Email Address</label>
                        <input type="email" name="email_address" id="email_address" class="form-control" value="{{ $isProspect ? '' : optional($customer)['email']}}" required>
                    </div>

                    @if(!$isProspect)

                        <div class="col-12 col-lg-8 mt-1">
                            <label for="templateSelector">Email Template</label>
                            <select id="templateSelector" onchange="populateEmailTemplate(this.value)" class="form-control select2 mt-1" data-placeholder="Select a Template">
                                <option value="">Select a Template</option>
                                @foreach ($templates as $index => $template)
                                    <option value="{{ $index }}">{{ $template['subject'] }}</option>
                                @endforeach
                            </select>
                        </div>

                    @endif

                    <div class="col-12 col-lg-8 mt-1">
                        <label for="subject">Email Subject</label>
                        <textarea name="subject" id="subject" class="form-control" rows="1" placeholder="Enter your email subject" aria-label="Email Subject" required></textarea>
                    </div>


                    <div class="col-12 col-lg-10 mt-1">
                        <label for="emailReservations">Promo Code Reservations</label>
                        <select id="emailReservations" name="reservations[]" class="form-control select2" multiple>

                            @if(!$isProspect)
                                @foreach(@$customerReservations as $res)

                                    <option
                                            value="{{ $res->uuid }}"
                                            data-banner="{{ $res->banner }}"
                                    >
                                        {{$res->code}}
                                    </option>

                                @endforeach
                            @endif

                        </select>
                    </div>


                    <div class="col-12 col-lg-10 mt-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="reservationBanners">Reservation Banners</label>
                            <button id="copyButton" class="btn btn-secondary btn-sm">Copy to Email</button>
                        </div>
                        <textarea id="reservationBanners" class="col-8 col-lg-8 tinyinput-small" cols="30" rows="10" aria-label="Email Content"></textarea>
                    </div>






                    <div class="col-12 col-lg-10 mt-1">
                        <label for="email">Email Content</label>
                        <textarea name="content" id="content" class="col-8 col-lg-8 tinyinput" cols="30" rows="10" placeholder="Enter your email content" aria-label="Email Content"></textarea>
                    </div>
                </div>

                <div class="edit-form-btn mt-3">
                    {{ link_to_route('biller.recent-customers.index', 'Cancel', [], ['class' => 'btn btn-danger btn-md mr-1']) }}
                    {{ Form::submit('Send Email', ['class' => 'btn btn-success btn-md','id'=>'e_btn']) }}
                    <div class="clearfix"></div>
                </div>

                {{ Form::close() }}
            </div>


            {{-- Sms tab --}}
            <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">
                @php

                    if ((!$isProspect)){

                        $sender = \Illuminate\Support\Facades\Auth::user();

                        $smsTemplate1 = 'Dear ' . optional($customer)['name'] . ', I hope this message finds you well. Following up on the recent project titled "' . optional($customer)['last_invoice_title'] . '" completed on ' . optional($customer)['last_invoice_date'] . ', we are here to ensure your satisfaction. Feel free to reach out if you have any questions or require further assistance. Regards, ' . $sender->fullname . ', ' . $sender->business->cname;

                        $smsTemplate2 = 'Hello ' . optional($customer)['name'] . ', Thank you for trusting us with "' . optional($customer)['last_invoice_title'] . '", completed on ' . optional($customer)['last_invoice_date'] . '. Are there any upcoming initiatives where we can assist? Let us know how we can add value to your operations. Best, ' . $sender->fullname . ', ' . $sender->business->cname;

                        $smsTemplate3 = 'Hi ' . optional($customer)['name'] . ', It’s a pleasure working with you, especially on "' . optional($customer)['last_invoice_title'] . '" (' . optional($customer)['last_invoice_date'] . '). We have exclusive offers available! Let us know if there’s anything we can help you with. Kind regards, ' . $sender->fullname . ', ' . $sender->business->cname;
                    }
                @endphp

                <form action="{{ $isProspect ? route('biller.sms-prospect') : route('biller.sms-recent-customer', optional($customer)['id']) }}" method="post" class="form-horizontal" id="sms-form">
                    @csrf
                    <div class="row">


                        @if($isProspect)
                            <div class="col-12 col-lg-8 mb-1">
                                <label for="email_address">Prospect Name</label>
                                <input type="text" name="prospect_name" id="prospect_name" class="form-control" required>
                            </div>
                        @endif


                        <div class="col-12 col-lg-8">

                            <label for="phone_number">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ $isProspect ? '' : optional($customer)['phone']}}" required>

                        </div>


                        @if(!$isProspect)

                            <!-- SMS Template Selector -->
                            <div class="col-12 col-lg-8 mt-1">
                                <label for="smsTemplateSelector">SMS Template</label>
                                <select id="smsTemplateSelector" onchange="populateSmsInput(this.value)" class="form-control select2 mt-1" data-placeholder="Select an SMS Template">
                                    <option value="">Select a Template</option>
                                    <option value="{{ $smsTemplate1 }}">Service Follow-Up</option>
                                    <option value="{{ $smsTemplate2 }}">New Opportunities</option>
                                    <option value="{{ $smsTemplate3 }}">Exclusive Offers</option>
                                </select>
                            </div>

                        @endif

                        <!-- SMS Content -->
                        <div class="col-12 col-lg-10 mt-1">
                            <label for="sms_content">SMS Content</label>
                            <textarea name="sms_content" id="sms_content" class="form-control" rows="6" placeholder="Enter your SMS content" aria-label="SMS Content"></textarea>
                        </div>
                    </div>

                    <!-- Form Buttons -->
                    <div class="edit-form-btn mt-3">
                        <a href="{{ route('biller.recent-customers.index') }}" class="btn btn-danger btn-md mr-1">Cancel</a>
                        <button type="submit" class="btn btn-success btn-md" id="sms_btn">Send SMS</button>
                    </div>
                </form>
            </div>


        </div>
    </div>
</div>

@section('after-scripts')
{{ Html::script('focus/js/jquery.password-validation.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>

    $('#login_access').change(function () {

        if (parseInt($(this).val()) === 1) {
            let confirmed = confirm(
                'Please note that this will allow the User to access your company information on the ERP. \nAre you sure the employee needs the access?'
            );

            if (!confirmed) $(this).val(0);
        }
    });



        // check all roles
    $('#check_all').change(function() {
        if ($(this).prop('checked')) {
            $('.permission').each(function(i) {
                $(this).prop('checked', true);
            })
        } else {
            $('.permission').each(function(i) {
                $(this).prop('checked', false);
            })
        }
    });
    
    $('.select2').select2({allowClear: true});

    $(document).ready(function () {

        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 560,
        });

        tinymce.init({
            selector: '.tinyinput-small',
            menubar: '',
            plugins: '',
            toolbar: '',
            height: 300,
            readonly  : true,
        });

        let reservationBannerContent = '';
        
        $('#emailReservations').on('select2:select', function (e) {

            if (!@json($isProspect)) {

                // Get the selected option
                var selectedOption = e.params.data.element;

                // Retrieve the data-banner attribute of the selected option
                var bannerContent = $(selectedOption).data('banner');


                console.table(bannerContent);
                
                reservationBannerContent += bannerContent;

                tinymce.get('reservationBanners').setContent(reservationBannerContent);

                // Append the new banner content dynamically
                // tinymce.get('content').setContent(tinymce.get('content').getContent() + bannerContent);
            }
            else {

                let reservations = $(this.val);

                // Append the new banner content dynamically
                tinymce.get('reservationBanners').setContent(getProspectBanners());
            }


        });


        $('#emailReservations').change(function (){

            if($(this.val).length === 0) tinymce.get('reservationBanners').setContent('');
        });


        function getProspectBanners(){

            reservationBannerContent = '';

            const selectedReservations = $('#emailReservations').val();

            console.log($('#emailReservations').val());

            selectedReservations.forEach((res, index) => {

                const bannerIndex = banners.findIndex(b => b.uuid === res);
                reservationBannerContent += banners[bannerIndex].content;
            });

            return reservationBannerContent;
        }




        // Array to hold banners mapped by option ID
        let banners = [];

        function loadThirdPartyReservations() {
            let isProspect = @json($isProspect);

            console.table(isProspect);


            if (isProspect) {
                console.log('Loading Prospect Reservations...');

                $('#emailReservations').select2({
                    ajax: {
                        url: '{{ route("biller.get-3p-reservations") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                                email: $('#email_address').val(),
                            };
                        },
                        processResults: function (data) {

                            // Map banners for later use
                            banners = [];

                            data.forEach(function (reservation) {
                                banners.push({ uuid: reservation.uuid, content: reservation.banner });
                            });

                            console.table(banners);

                            // Return options for the dropdown
                            return {
                                results: data.map(function (reservation) {
                                    return {
                                        id: reservation.uuid,
                                        text: reservation.code,
                                    };
                                }),
                            };
                        },
                        cache: true,
                    },
                });
            }
        }


        $('#email_address').change(function () {

            console.log("CHANGED EMAIL!!!")

            $('#emailReservations').val(null).trigger('change');

            loadThirdPartyReservations();
        });



        $('#copyButton').click( function (e) {

            e.preventDefault();

            let isProspect = @json($isProspect);

            if(isProspect) {

                tinymce.get('content').setContent(tinymce.get('content').getContent() + getProspectBanners());
                if (getProspectBanners().length) alert("Promo Code Reservation Banners have been pasted at the end of your email.")
            }
            else {

                tinymce.get('content').setContent(tinymce.get('content').getContent() + reservationBannerContent);
                if (reservationBannerContent.length) alert("Promo Code Reservation Banners have been pasted at the end of your email.")
            }


        });

    });

    if(!@json($isProspect)) {

        const templates = @json($templates);

        function populateEmailTemplate(index) {
            const template = templates[index];
            if (template) {
                document.getElementById('subject').value = template.subject;
                tinymce.get('content').setContent(template.content);
            }
        }
    }

    function populateSmsInput(template) {
        document.getElementById('sms_content').value = template;
    }


    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });


</script>
@endsection
