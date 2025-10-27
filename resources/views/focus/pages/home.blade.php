@extends('core.layouts.apps')
@section('title', 'Home')

<style>
    .home-wrapper {
        padding: 1.2rem 1rem;
        padding-bottom: 5rem !important; /* 👈 Prevent overlap with bottom nav */
    }

    .title {
        font-size: 1.6rem;
        font-weight: 700;
    }

    .cta-btn {
        background: #007bff;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        padding: 0.9rem;
        transition: 0.2s;
    }

    .cta-btn:hover {
        background: #0069d9;
    }

    .list-group-item {
        border-radius: 10px !important;
        margin-bottom: 6px;
        border: 1px solid #e8e8e8;
    }

    /* ✅ Fixed Bottom Nav across devices */
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

    /* ✅ Larger Screen Enhancements */
    @media (min-width: 768px) {
        .title {
            font-size: 2rem;
        }
        .cta-btn {
            width: 60%;
            margin: 0 auto;
            font-size: 1.2rem;
        }
        .nav-pill i {
            font-size: 1.4rem;
        }
    }
</style>

@section('content')
<div class="row g-3 g-xl-4 home-wrapper">
    <section class="col-12">
        <div class="glass-card pane h-100">
            <h2 class="title lh-sm">
                Hi {{ $customer->name }},<br>Need a refill?
            </h2>

            <button class="btn btn-lg cta-btn w-100 mt-3"
                onclick="window.location.href='{{ route('biller.customer_pages.orders') }}'">
                Order Now
            </button>

            @if ($incoming_schedules->count())
            <div class="mt-4">
                <h5>Incoming Delivery</h5>
                <ul class="list-group small">
                    @foreach ($incoming_schedules as $sch)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ gen4tid('ORD-', $sch->order->tid) }}</span>
                            <span class="badge bg-info text-uppercase">{{ ucfirst($sch->status) }}</span>
                            <strong>{{ \Carbon\Carbon::parse($sch->delivery_date)->format('M d, Y') }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if ($prev_schedules->count())
            <div class="mt-4">
                <h5>Recent Deliveries</h5>
                <ul class="list-group small">
                    @foreach ($prev_schedules as $sch)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ gen4tid('ORD-', $sch->order->tid) }}</span>
                            <span class="text-success">Delivered</span>
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($sch->delivery_date)->format('M d, Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </section>
</div>

{{-- ✅ Bottom Nav --}}
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

        <a class="nav-pill {{ request()->routeIs('biller.customer_pages.profile') ? 'active' : '' }}"
            href="{{ route('biller.customer_pages.profile') }}">
            <i class="bi bi-person"></i><span>Profile</span>
        </a>
    </div>
</div>
@endsection
