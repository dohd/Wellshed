@php use App\Models\Access\User\User; @endphp
@extends ('core.layouts.app')

@section ('title', 'Recent Customer Communication')

@section('content')

@include('tinymce.scripts')

<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Recent Customer Communication </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">

                    <div class="btn-group" role="group" aria-label="Basic example">

                        <a href="{{ route( 'biller.recent-customer-messages' ) }}" class="btn btn-info  btn-lighten-2 round"><i
                                    class="fa fa-list-alt"></i> {{trans( 'general.list' )}}</a>

                    </div>

                </div>
            </div>
        </div>

    </div>


    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            <div class="row">

                                <div class="col-12 col-lg-9">
                                    <label for="sender">Sent To</label>
                                    <input type="text" id="sender" class="form-control" value="{{ $payload->customer ? optional($payload->customer)->company : $payload->prospect_name }}" readonly>
                                </div>

                                <div class="col-12 col-lg-6 mt-1">
                                    <label for="sender">Sent By</label>
                                    @php
                                        $sender = User::withoutGlobalScopes()->find($payload->created_by);
                                    @endphp
                                    <input type="text" id="sender" class="form-control" value="{{ $sender->fullname }}" readonly>
                                </div>

                                <div class="col-12 col-lg-3 mt-1">
                                    <label for="date">Date</label>
                                    <input type="text" id="date" class="form-control" value="{{ (new DateTime($payload->created_at))->format('l, jS F, Y') }}" readonly>
                                </div>


                                @if($isEmail)

                                    <div class="col-12 col-lg-4 mt-1">
                                        <label for="email_address">Email Address</label>
                                        <input type="email" id="email_address" class="form-control" value="{{$payload['email_address']}}" readonly>
                                    </div>

                                    <div class="col-12 col-lg-6 mt-1">
                                        <label for="subject">Email Subject</label>
                                        <textarea name="subject" id="subject" class="form-control" rows="1" placeholder="Enter your email subject" aria-label="Email Subject" readonly>{{ $payload['subject'] }}</textarea>

                                    </div>

                                    @if($payload->customerPromoCodeReservations->first() || $payload->thirdPartyPromoCodeReservations->first())

                                        <div class="col-12 col-lg-10 mt-3">

                                            <h4> Shared Promotional Code Reservations  </h4>

                                            @php
                                                $reservations = $payload->customerPromoCodeReservations ?? $payload->thirdPartyPromoCodeReservations;
                                            @endphp

                                            @foreach($reservations as $res)

                                                @php
                                                    $validFrom = (new DateTime($res->reserved_at))->format('jS M Y, g:iA');
                                                    $validUntil = (new DateTime($res->expires_at))->format('jS M Y, g:iA');
                                                    $period = "<span style='font-size:14px'>Valid from <b>{$validFrom}</b> to <b>{$validUntil}</b></span>";
                                                @endphp

                                            <p>
                                                <span style="font-size: 16px"><b>{{ $res->promoCode->code }}</b></span>:  {!! $period !!}
                                            </p>


                                            @endforeach

                                        </div>

                                    @endif

                                    <div class="col-12 col-lg-10 mt-1">
                                        <label for="email">Email Content</label>
                                        <textarea id="content" class="col-8 col-lg-8 tinyinput" cols="30" rows="10" placeholder="Enter your email content" aria-label="Email Content">
                                            {{ $payload['content'] }}
                                        </textarea>
                                    </div>

                                @else

                                    <div class="col-12 col-lg-4 mt-1">
                                        <label for="phone_number">Phone Number</label>
                                        <input type="text" id="phone_number" class="form-control" value="{{$payload['phone_number']}}" readonly>
                                    </div>

                                    <br>

                                    <!-- SMS Content -->
                                    <div class="col-12 col-lg-10 mt-1">
                                        <label for="sms_content">SMS Content</label>
                                        <textarea id="sms_content" class="form-control tinyinput-tiny" rows="6" placeholder="Enter your SMS content" aria-label="SMS Content">{{ $payload['content'] }}</textarea>
                                    </div>

                               @endif




                            </div>



                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script>

    tinymce.init({
        selector: '.tinyinput',
        menubar: '',
        plugins: '',
        toolbar: '',
        height: 560,
        readonly  : true,
    });

    tinymce.init({
        selector: '.tinyinput-tiny',
        menubar: '',
        plugins: '',
        toolbar: '',
        height: 200,
        readonly  : true,
    });

</script>
@endsection