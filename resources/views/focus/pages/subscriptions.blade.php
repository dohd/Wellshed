@extends('core.layouts.apps')
@section('title', 'Water Delivery Subscriptions')

<style>
    .glass-card {
        background: #ffffff;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .p-title {
        font-weight: bold;
        color: #007bff;
    }
    .pkg-card {
        border: 1px solid #e4e4e4;
        border-radius: 12px;
        padding: 1rem;
        transition: transform 0.2s ease;
    }
    .pkg-card:hover {
        transform: translateY(-3px);
    }
    .price {
        font-size: 1.2rem;
        font-weight: 700;
        color: #28a745;
    }
    .benefit {
        font-size: 14px;
        color: #555;
    }
    .sub-status {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 10px;
        margin-bottom: 6px;
        display: inline-block;
    }
    .active-status {
        background: #d6f5d6;
        color: #0c6b1a;
    }
    .expired-status {
        background: #ffe0e0;
        color: #b10606;
    }

    /* ✅ Footer Nav */
    .footer-nav {
        position: fixed;
        bottom: 0;
        width: 100%;
        background: #fff;
        padding: .3rem 0;
        border-top: 1px solid #ddd;
        z-index: 999;
    }
    .nav-pill {
        flex: 1;
        text-align: center;
        color: #666;
        font-size: 12px;
        text-decoration: none;
    }
    .nav-pill i {
        font-size: 20px;
        display: block;
    }
    .nav-pill.active {
        color: #007bff;
        font-weight: bold;
    }
</style>

@section('content')
<div class="container pb-5">

    <h3 class="mb-3 fw-bold">My Subscription</h3>

    {{-- ✅ Active Subscription (Dummy Example) --}}
    <div class="glass-card">
        <span class="sub-status active-status">Active</span>
        <h5 class="fw-bold">Wave Plan</h5>
        <p class="mb-1">8 Bottles Monthly</p>
        <p class="price mb-1">KSh 4,000 / month</p>
        <p class="text-muted small mb-2"><strong>Next Delivery:</strong> Nov 02, 2025</p>

        <a href="#" class="btn btn-sm btn-outline-primary">View Subscription</a>
    </div>


    {{-- ✅ Available Packages Section --}}
    <h4 class="p-title mt-4">Available Subscription Packages</h4>

    {{-- FLOW PLAN --}}
    <div class="pkg-card mt-3">
        <h5 class="fw-bold">Flow Plan</h5>
        <p class="price">KSh 2,000 / month</p>

        <ul class="benefit mt-2">
            <li>Up to 4 bottles per month</li>
            <li>Loyalty point rewards</li>
        </ul>

        <a href="#" class="btn btn-primary w-100 mt-2">Subscribe</a>
    </div>

    {{-- WAVE PLAN --}}
    <div class="pkg-card mt-3">
        <h5 class="fw-bold">Wave Plan</h5>
        <p class="price">KSh 4,000 / month</p>

        <ul class="benefit mt-2">
            <li>Up to 8 bottles per month</li>
            <li>Loyalty point rewards</li>
            <li>1 case free disposable cups every 3 months (Corporate)</li>
        </ul>

        <a href="#" class="btn btn-primary w-100 mt-2">Subscribe</a>
    </div>

    {{-- TIDE PLAN --}}
    <div class="pkg-card mt-3">
        <h5 class="fw-bold">Tide Plan</h5>
        <p class="price">KSh 6,000 / month</p>

        <ul class="benefit mt-2">
            <li>Up to 12 bottles per month</li>
            <li>Loyalty point rewards</li>
            <li>1 case free disposable cups every 3 months (Corporate)</li>
            <li>Same-day delivery (Kilimani & Upper Hill)</li>
        </ul>

        <a href="#" class="btn btn-primary w-100 mt-2">Subscribe</a>
    </div>

    {{-- ✅ Previous Subscriptions --}}
    <h4 class="p-title mt-4">Previous Subscriptions</h4>

    <div class="glass-card">
        <span class="sub-status expired-status">Expired</span>
        <h6 class="fw-bold mb-1">Flow Plan</h6>
        <p class="text-muted mb-1 small">Ended: Sep 15, 2025</p>
        <a href="#" class="btn btn-sm btn-danger">Reactivate</a>
    </div>

</div>

{{-- ✅ Bottom Navigation --}}
<div class="footer-nav">
    <div class="d-flex justify-content-around">
        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.home') ? 'active' : '' }}"
           href="{{ route('biller.customer_pages.home') }}">
            <i class="bi bi-house"></i><span>Home</span>
        </a>

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.orders') ? 'active' : '' }}"
           href="{{ route('biller.customer_pages.orders') }}">
            <i class="bi bi-receipt"></i><span>Orders</span>
        </a>

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.subscriptions') ? 'active' : '' }}"
           href="{{ route('biller.customer_pages.subscriptions') }}">
            <i class="bi bi-bag-check"></i><span>Subscriptions</span>
        </a>

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.profile') ? 'active' : '' }}"
           href="{{ route('biller.customer_pages.profile') }}">
            <i class="bi bi-person"></i><span>Profile</span>
        </a>

        <a class="nav-pill" href="{{ route('biller.logout') }}">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </div>
</div>
@endsection
