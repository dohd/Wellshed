@extends('core.layouts.apps')

@section('title', 'No Subscription')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="text-warning mb-3">
                        Get Started
                    </h3>

                    <p class="text-muted">
                        You don’t have a subscription yet.
                        Choose a plan to start placing orders.
                    </p>

                    <hr>

                    <a href="{{ route('biller.customer_pages.subscriptions') }}"
                       class="btn btn-success">
                        Choose a Plan
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
