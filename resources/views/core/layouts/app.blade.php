@php
    use App\Models\Access\User\User;use App\Models\marquee\SuperAdminMarquee;use App\Models\marquee\UserMarquee;
@endphp
        <!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="{{visual()}}">
<!-- BEGIN: Head-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Rose Billing')">
    @yield('meta')
    <title>@yield('title', app_name())</title>
    <link rel="shortcut icon" type="image/x-icon"
          href="{{ Storage::disk('public')->url('app/public/img/company/ico/' . config('core.icon')) }}">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i%7COpen+Sans:300,300i,400,400i,600,600i,700,700i"
          rel="stylesheet">
    <script type="text/javascript">
        var baseurl = '{{route('biller.index')}}/';
        var crsf_token = 'csrf-token';
        var crsf_hash = '{{ csrf_token() }}';
        window.Laravel = {!! json_encode([ 'csrfToken' => csrf_token() ]) !!};
        var unit_load_data = {!!units() !!};
        var unit_load_tax = {!!taxes() !!};
        var cur_dy = '{{config('currency.symbol')}}';
    </script>
    <!-- BEGIN: Vendor CSS-->
    {{ Html::style(mix('focus/app_end-'.visual().'.css')) }}
    {!! Html::style('core/app-assets/css-'.visual().'/core/menu/menu-types/horizontal-menu.css') !!}
    {!! Html::style('core/app-assets/vendors/css/forms/icheck/icheck.css') !!}
    {!! Html::style('core/app-assets/vendors/css/forms/icheck/custom.css') !!}
    @yield('after-styles')
    <!-- END: Vendor CSS-->
    <!-- BEGIN: Custom CSS-->
    {!! Html::style('core/assets/css/style-'.visual().'.css') !!}
    <!-- END: Custom CSS-->
    <meta name="d_unit" content="{{trans('productvariables.unit_default')}}">
</head>
<!-- END: Head-->
@if(isset($page))
    <body {!!$page !!} >
    @else
        <body class="horizontal-layout horizontal-menu 2-columns " data-open="click" data-menu="horizontal-menu"
              data-col="2-columns">
        @endif
        @if ($logged_in_user)
            @include('core.partials.menu')
        @endif


        <div id="c_body"></div>
        @if(session('flash_success'))
            <div class="alert bg-success alert-dismissible m-1" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <strong>Success!</strong> {!!session('flash_success')!!}
            </div>
        @endif
        @if(session('flash_error'))
            <div class="alert bg-danger alert-dismissible m-1" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <strong>Error!</strong> {!!session('flash_error')!!}
            </div>
        @endif
        @if($logged_in_user)
            @if(@$errors->any())
                <div class="alert bg-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <strong>Error!</strong> {!! implode('', $errors->all(':message'))  !!}
                </div>
            @endif
        @endif


        @php


            if ($logged_in_user){

                $now = (new DateTime())->format('Y-m-d H:i:s');

    //            $superAdminMarquee = SuperAdminMarquee::whereNull('business')->where('start', '<=', $now)->where('end', '>=', $now)->first() ?
    //                SuperAdminMarquee::whereNull('business')->where('start', '<=', $now)->where('end', '>=', $now)->first()->content :
    //                "";
                $businessMarquee = SuperAdminMarquee::where('business', \Illuminate\Support\Facades\Auth::user()->ins)->where('start', '<=', $now)->where('end', '>=', $now)->first() ?
                    SuperAdminMarquee::where('business', \Illuminate\Support\Facades\Auth::user()->ins)->where('start', '<=', $now)->where('end', '>=', $now)->first()->content :
                     "";
                $userMarquee = UserMarquee::where('start', '<=', $now)->where('end', '>=', $now)->first() ?
                    UserMarquee::where('start', '<=', $now)->where('end', '>=', $now)->first()->content :
                    "";

                $adminIns = User::find(130) ? User::find(130)->ins : false;
                $isAdmin = Auth::user()->ins === $adminIns;

                $isKe = User::where('id', 130)->where('ins', 2)->first();

            }
        @endphp

        {{--        @if($superAdminMarquee && !empty($isKe))--}}
        {{--            <div class="running-message" style="font-size: 16px; background-color: white">--}}
        {{--                <p style="color: black"> <span style="font-size: 22px; color: green;"><i>Announcement For All Businesses on PME: </i></span>{{ $superAdminMarquee }}</p>--}}
        {{--            </div>--}}
        {{--        @endif--}}

        @if($logged_in_user)

            @if($businessMarquee && !empty($isKe))
                <div class="running-message" style="font-size: 16px; background-color: white">
                    <p style="color: black"><span style="font-size: 22px; color: #F3C400;"><i>Message for '{{ Auth::user()->business->cname }}' from PME: </i></span>{{ $businessMarquee }}
                    </p>
                </div>
            @endif

            @if($userMarquee)
                <div class="running-message mt-1" style="font-size: 16px; background-color: white">
                    <p style="color: black"><span
                                style="font-size: 22px; color: #0D499E;"><i>Message from Your Management: </i></span>{{ $userMarquee }}
                    </p>
                </div>
            @endif

        @endif

        <div class="app-content content">
            @yield('content')
        </div>
        {{ Html::script(mix('js/app_end.js')) }}
        {{ Html::script('focus/js/control.js?b='.config('version.build')) }}
        {{ Html::script('focus/js/custom.js?b='.config('version.build')) }}
        <script type='text/javascript'>
            accounting.settings = {
                number: {
                    precision: "{{config('currency.precision_point')}}",
                    thousand: "{{config('currency.thousand_sep')}}",
                    decimal: "{{config('currency.decimal_sep')}}"
                }
            };
            var two_fixed = "{{config('currency.precision_point')}}";
            var currency = "{{config('currency.symbol')}}";

            function editor() {
                $('.html_editor').summernote({
                    height: 60,
                    tooltip: false,
                    toolbar: [
                        {!! config('general.editor') !!}
                    ],
                    popover: {}

                });
            }
        </script>
        @yield('after-scripts')
        @yield('extra-scripts')

        </body>
</html>


<style>
    .running-message {
        width: 100%; /* Full width */
        height: 50px; /* Set the height of the container */
        overflow: hidden; /* Hide the overflowing text */
        background-color: #f2f2f2; /* Background color */
        white-space: nowrap; /* Prevent the text from wrapping */
        display: flex; /* Use flexbox */
        align-items: center; /* Vertically center the text */
    }

    .running-message p {
        display: inline-block;
        padding-left: 100%; /* Start the text off-screen */
        animation: scroll-text 45s linear infinite; /* Slower Animation */
        margin: 0; /* Remove default margin */
    }

    @keyframes scroll-text {
        from {
            transform: translateX(0); /* Start from the right */
        }
        to {
            transform: translateX(-100%); /* End at the left */
        }
    }

</style>
