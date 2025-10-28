@extends('core.layouts.apps')
@section('title', 'Payments')

<style>
    .page-wrapper {
        padding: 1.2rem 1rem;
        padding-bottom: 5rem !important;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .card-box {
        background: #fff;
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid #e8e8e8;
        margin-bottom: 1.2rem;
    }

    .wallet-balance {
        font-size: 2rem;
        font-weight: bold;
        color: #007bff;
    }

    .list-group-item {
        border-radius: 10px !important;
        border: 1px solid #e8e8e8;
        margin-bottom: 6px;
    }

    .add-payment-btn {
        background: #007bff;
        border-radius: 10px;
        padding: 0.85rem;
        font-weight: 600;
        transition: 0.2s;
    }

    .add-payment-btn:hover {
        background: #0069d9;
    }

    /* ✅ Fixed Bottom Nav */
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

    .footer-nav-space {
        height: 60px;
    }
</style>

@section('content')

<div class="page-wrapper">

    {{-- ✅ Wallet Summary --}}
    <div class="card-box text-center">
        <p class="text-muted mb-1">Wallet Balance</p>
        <h2 class="wallet-balance">KSh 12,500.00</h2>

        <button class="btn w-100 mt-3 add-payment-btn"
            onclick="alert('Top-Up payment coming soon')">
            + Add Payment
        </button>
    </div>

    {{-- ✅ Recent Payments --}}
    <h4 class="section-title">Recent Transactions</h4>

    <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between">
            <span>Wallet Top-up</span>
            <span class="text-success">+ KSh 5,000</span>
            <small class="text-muted">Oct 10, 2025</small>
        </li>

        <li class="list-group-item d-flex justify-content-between">
            <span>Order #2432</span>
            <span class="text-danger">- KSh 3,500</span>
            <small class="text-muted">Oct 06, 2025</small>
        </li>

        <li class="list-group-item d-flex justify-content-between">
            <span>Order #2307</span>
            <span class="text-danger">- KSh 2,700</span>
            <small class="text-muted">Sep 29, 2025</small>
        </li>

        <li class="list-group-item d-flex justify-content-between">
            <span>Wallet Top-up</span>
            <span class="text-success">+ KSh 10,000</span>
            <small class="text-muted">Sep 20, 2025</small>
        </li>
    </ul>

</div>

{{-- Spacer --}}
<div class="footer-nav-space"></div>

{{-- ✅ Footer Navigation --}}
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

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.payments') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.payments') }}">
            <i class="bi bi-credit-card"></i>
            <span>Payments</span>
        </a>

        <a class="nav-pill" href="{{ route('biller.logout') }}">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

@endsection
