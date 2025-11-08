@extends('core.layouts.apps')
@section('title', 'Order Complete')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="text-center mt-5">
                <h3>✅ Order Submitted Successfully!</h3>
                <p>Thank you for your order. We will notify you soon.</p>

                {{-- <a href="{{ route('biller.customer_pages.orders') }}" class="btn btn-primary mt-3">
                    Make Another Order
                </a> --}}

                <button id="backBtn" class="btn btn-secondary mt-3">
                    Home
                </button>
            </div>
        </div>
    </div>
@endsection

@section('extra-scripts')
    <!-- jQuery (include only if not already loaded) -->
    <script>
        $(document).ready(function () {
            $("#backBtn").on("click", function () {
                localStorage.clear();   // ✅ Clear localStorage
                window.location.href = "{{ route('biller.customer_pages.home') }}"; // ✅ Go to home
            });
        });
    </script>
@endsection
