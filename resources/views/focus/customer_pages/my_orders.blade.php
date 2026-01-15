@extends('core.layouts.apps')
@section('title', 'My Orders')

<style>
    .orders-wrapper {
        padding: 1.2rem 1rem;
        padding-bottom: 5rem !important;
    }

    .order-card {
        background: #fff;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #e8e8e8;
        margin-bottom: 10px;
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .order-card:hover {
        background: #f7faff;
        border-color: #007bff;
        transition: .25s;
    }

    .order-id {
        font-size: 1rem;
        font-weight: 600;
    }

    .order-status {
        font-size: 0.8rem;
        font-weight: bold;
        padding: 4px 10px;
        border-radius: 5px;
        text-transform: capitalize;
    }

    .status-confirmed { background: #e8f7ff; color: #007bff; }
    .status-started { background: #fff4d3; color: #b88600; }
    .status-completed { background: #eaffea; color: #1a7f1a; }
    .status-cancelled { background: #ffeaea; color: #d10000; }

    .footer-nav-space {
        height: 60px;
    }

    /* ✅ Bottom Nav */
    .footer-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #ffffff;
        border-top: 1px solid #ddd;
        padding: .4rem 0;
        z-index: 9999;
    }

    .nav-pill {
        font-size: .75rem;
        color: #666;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        flex: 1;
    }

    .nav-pill i {
        font-size: 1.1rem;
    }

    .nav-pill.active {
        color: #007bff;
        font-weight: 600;
    }
</style>

@section('content')

<div class="orders-wrapper">

    <h3 class="mb-3 fw-bold">My Orders</h3>

    @forelse ($orders as $order)
        <a href="#" class="order-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="order-id">{{ gen4tid('ORD-', $order->tid) }}</span>
                <span class="order-status status-{{ strtolower($order->status) }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <div class="mt-2 d-flex justify-content-between">
                <small class="text-muted">
                    {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                </small>
                <strong>KSh {{ number_format($order->total, 2) }}</strong>
            </div>
        </a>
    @empty
        <p class="text-center text-muted mt-4">No orders found.</p>
    @endforelse

</div>

{{-- Spacer --}}
<div class="footer-nav-space"></div>

{{-- ✅ Bottom Navigation --}}
<div class="footer-nav">
    <div class="d-flex justify-content-around">
        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.home') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.home') }}">
            <i class="bi bi-house"></i>
            <span>Home</span>
        </a>

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.orders') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.orders') }}">
            <i class="bi bi-receipt"></i>
            <span>Orders</span>
        </a>

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.profile') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.profile') }}">
            <i class="bi bi-person"></i>
            <span>Profile</span>
        </a>

        {{-- <a class="nav-pill {{ request()->routeIs('biller.customer_pages.payments') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.payments') }}">
            <i class="bi bi-credit-card"></i>
            <span>Payments</span>
        </a> --}}

        <a class="nav-pill" href="{{ route('biller.logout') }}">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

@endsection
