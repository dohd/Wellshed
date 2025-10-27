@extends('core.layouts.apps')
@section('title', 'Order Complete')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="text-center mt-5">
                <h3>✅ Order Submitted Successfully!</h3>
                <p>Thank you for your order. We will notify you soon.</p>

                <a href="{{ route('biller.customer_pages.orders') }}" class="btn btn-primary mt-3">
                    Make Another Order
                </a>
            </div>
        </div>
    </div>
@endsection
