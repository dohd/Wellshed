@extends('core.layouts.apps')

@section('title', 'Subscription Expired')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="text-danger mb-3">
                        Subscription Expired
                    </h3>

                    <p class="text-muted">
                        Your subscription has expired or is no longer active.
                        To continue placing orders, please renew your subscription.
                    </p>

                    <hr>

                    <a href="{{ route('biller.customer_pages.payments') }}"
                       class="btn btn-primary">
                        Pay & Renew Subscription
                    </a>

                    <br><br>

                    <a href="{{ route('biller.customer_pages.home') }}"
                       class="text-muted">
                        Back to Dashboard
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
