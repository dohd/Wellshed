@extends('core.layouts.apps')
@section('title', 'Profile')

<style>
    /* ✅ Bottom Navigation */
    .footer-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #ffffff;
        border-top: 1px solid #ddd;
        padding: 0.4rem 0;
        z-index: 9999;
    }

    .nav-pill {
        text-align: center;
        flex: 1;
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
    }
</style>

@section('content')
<div class="container py-4" style="padding-bottom: 80px !important;">
    <div class="card shadow-sm border-0">
        <div class="card-header text-center bg-primary text-white">
            <h5 class="mb-0">Customer Profile</h5>
        </div>
        <div class="card-body">

            <div class="mb-3">
                <label class="fw-bold text-secondary">Name</label>
                <div class="p-2 rounded bg-light border">
                    {{ $customer->name ?? 'N/A' }}
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold text-secondary">Email</label>
                <div class="p-2 rounded bg-light border">
                    {{ $customer->email ?? 'N/A' }}
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold text-secondary">Phone</label>
                <div class="p-2 rounded bg-light border">
                    {{ $customer->phone ?? 'N/A' }}
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold text-secondary">Address</label>
                <div class="p-2 rounded bg-light border">
                    {{ $customer->address ?? 'N/A' }}
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ✅ Bottom Navigation --}}
<div class="footer-nav d-md-none"> {{-- hidden on desktop --}}
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

        <a class="nav-pill" href="{{ route('biller.logout') }}">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
@endsection
