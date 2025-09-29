<!DOCTYPE html>

@include('tinymce.scripts')

@extends ('core.layouts.app')

@section ('title', 'Subscription Package Details')

@section('content')
    <div class="content-wrapper">

<!--        @permission('create-welcome-message')-->

            <div class="content-header row mb-1">
                <div class="content-header-left col-6">
                    <h2 class=" mb-0">Subscription Details</h2>
                </div>
            </div>

<!--        @endauth-->

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-radius: 8px;">
                        <div class="card-content">
                            <div class="card-body p-8">


                                <div class="row mb-3">

                                    <div class="col-12 col-lg-4 mr-2">

                                        <form method="post" id="grace-days-form" action="{{ route('biller.tenants.request-grace', Auth::user()->business->id) }}">
                                            {{ csrf_field() }}

                                            <h4 class="mt-2">Request Grace Days (Max: <span style="font-size: 22px;">7</span>)</h4>

                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <input type="number" step="1" id="days" name="days" class="form-control mb-1" placeholder="Enter days">

                                                <button type="submit" class="btn btn-success btn-approve">Request Days</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-12 col-lg-4">

                                        @if(Auth::user()->business->loyalty_points > 0)

                                            <!-- Second Form for the new input and button -->
                                            <form method="post" id="loyalty-points-form" action="{{ route('biller.tenants.redeem-loyalty-points', Auth::user()->business->id) }}">
                                                {{ csrf_field() }}

                                                <h4 class="mt-2">
                                                    Redeem Loyalty Points
                                                    <span>
                                                                    <b>Current Balance:</b>
                                                                    <span style="font-size: 22px; color: goldenrod;"> {{ number_format(Auth::user()->business->loyalty_points, 2) }} </span> pts
                                                                </span>
                                                </h4>

                                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                    <input type="number" step="1" id="points" name="points" class="form-control mb-1" placeholder="Enter days">

                                                    <button type="submit" class="btn btn-primary btn-approve">Redeem Loyalty Points</button>
                                                </div>

                                            </form>

                                        @endif

                                    </div>

                                </div>
                                <hr>


                                <div class="row mt-3" style="font-size: 18px;">

                                    <div class="col-12 col-lg-6">

                                        <h1 class="text-lg font-bold text-gray-700 mb-1">Package Information</h1>
                                        <p aria-label="Package Name" class="text-gray-600"><strong>Package Name:</strong> {{ $subscriptionDetails['package_name'] }}</p>
                                        <p aria-label="Subscription Price" class="text-gray-600"><strong>Monthly Subscription Fee:</strong> {{ $subscriptionDetails['subscription_price'] }}</p>
                                        <br>
                                        <p aria-label="Subscription Price" class="text-gray-600"><strong>Balance:</strong> {{ number_format(\Illuminate\Support\Facades\Auth::user()->business->subscription_balance, 2) }}</p>
                                        <p aria-label="Billing Date" class="text-gray-600"><strong>Billing Date:</strong> {{ $subscriptionDetails['billing_date'] }}</p>
                                        <p aria-label="Billing Date" class="text-gray-600"><strong>Grace Days:</strong> {{ \Illuminate\Support\Facades\Auth::user()->business->grace_days }}</p>
                                        <p aria-label="Cutoff Date" class="text-gray-600"><strong>Cutoff Date:</strong> {{ $subscriptionDetails['cutoff_date'] }}</p>


                                    </div>

                                    <div class="col-12 col-lg-6">


                                        <h1 class="text-lg font-bold text-gray-700 mb-1">Support</h1>

                                        @if(!$subscriptionDetails['rm_email'])

                                            <p aria-label="Relationship Manager" class="text-gray-600"><strong> <i>Relationship Manager:</i></strong>  <i><span style="color: goldenrod"> Pending Assignment </span> </i> </p>

                                        @else

                                            <p aria-label="Relationship Manager" class="text-gray-600"><strong> <i>Relationship Manager:</i></strong> {{ $subscriptionDetails['rm'] }}</p>
                                            <p aria-label="Relationship Manager's Email" class="text-gray-600"><strong>Email:</strong> {{ $subscriptionDetails['rm_email'] }}</p>
                                            <p aria-label="Relationship Manager's Phone" class="text-gray-600"><strong>Phone:</strong> {{ $subscriptionDetails['rm_phone'] }}</p>

                                        @endif

                                        <br>
                                        <br>

                                        @if($subscriptionDetails['agent_email'])

                                            <p aria-label="Sales Agent" class="text-gray-600"><strong> <i>Sales Agent:</i></strong> {{ $subscriptionDetails['agent'] }}</p>
                                            <p aria-label="Sales Agent's Email" class="text-gray-600"><strong>Email:</strong> {{ $subscriptionDetails['agent_email'] }}</p>
                                            <p aria-label="Sales Agent's Phone" class="text-gray-600"><strong>Phone:</strong> {{ $subscriptionDetails['agent_phone'] }}</p>

                                        @endif


                                    </div>

                                </div>


                                <div class="row" style="font-size: 20px;">

                                    <div class="bg-gray-50 p-2 rounded-md col-12 col-lg-6">
                                        <h1 class="text-lg font-bold text-gray-700 mb-1">Payment Information</h1>
                                        <p aria-label="Bank" class="text-gray-600"><strong>Bank:</strong> {{ $subscriptionDetails['bank'] }}</p>
                                        <p aria-label="Bank" class="text-gray-600"><strong>Branch:</strong> Gateway Branch</p>
                                        <p aria-label="Mpesa Paybill" class="text-gray-600"><strong>KCB Mpesa Paybill:</strong> {{ $subscriptionDetails['mpesa_paybill'] }}</p>
                                        <p aria-label="Account Number" class="text-gray-600"><strong>Account Number:</strong> {{ $subscriptionDetails['account'] }}</p>
                                        <p aria-label="Account Number" class="text-gray-600"><strong>Account Name:</strong> Mabiga Holding Ltd</p>
                                        <p aria-label="Account Number" class="text-gray-600"><strong>Swift Code:</strong> KCBLKENX </p>
                                    </div>

                                    <div class="bg-gray-50 p-2 rounded-md col-12 col-lg-6">
                                        <h1 class="text-lg font-bold text-gray-700 mb-1">Contact Information</h1>
                                        <p aria-label="Bank" class="text-gray-600"><strong>Email:</strong> sales@erpproject.co.ke </p>
                                        <p aria-label="Bank" class="text-gray-600"><strong>Contact:</strong> +254 788 175 400</p>


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
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    <script>


        $(document).ready(function () {
            $('#days').on('input', function () {
                let currentValue = $(this).val();
                if (currentValue > 7) {
                    $(this).val(7);
                }
            });


            $('#points').on('input', function () {

                let currentValue = $(this).val();
                let currPoints = parseFloat(@json(Auth::user()->business->loyalty_points));

                if (currentValue > currPoints) {
                    $(this).val(currPoints);
                }
            });



        });

        $(document).ready(function () {
            $('#grace-days-form').on('submit', function (e) {
                e.preventDefault(); // Prevent default submission to handle confirmation

                // Get the number of days from the input field
                const days = $('#days').val();

                // Show confirmation dialog
                if (confirm('Are you sure you want to request ' + days + ' grace day(s)?')) {
                    // If confirmed, submit the form
                    this.submit();
                }
            });
        });

        $(document).ready(function () {
            $('#loyalty-points-form').on('submit', function (e) {
                e.preventDefault(); // Prevent default submission to handle confirmation

                // Get the number of days from the input field
                const points = $('#points').val();

                // Show confirmation dialog
                if (confirm('Are you sure you want to redeem ' + points + ' loyalty points?')) {
                    // If confirmed, submit the form
                    this.submit();
                }
            });
        });

    </script>
@endsection
