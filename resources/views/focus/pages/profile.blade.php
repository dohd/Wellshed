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
        padding: .4rem 0;
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
<div class="glass-card pane h-100 pb-5">
    <h3 class="title mb-2">Your Profile</h3>
    <p class="text-muted">Profile information goes here…</p>
</div>

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
        <a class="nav-pill" href="{{ route('biller.logout') }}"><i class="ft-power"></i>
         <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </div>
</div>
@endsection

