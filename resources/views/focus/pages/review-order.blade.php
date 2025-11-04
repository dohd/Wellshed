@extends('core.layouts.apps')
@section('title', 'Review Order')

<style>
    .review-wrapper {
        padding: 1.2rem 1rem;
        padding-bottom: 5rem !important;
    }

    .list-group-item {
        border-radius: 10px !important;
        margin-bottom: 6px;
        border: 1px solid #e8e8e8;
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

    @media (min-width: 768px) {
        .nav-pill i {
            font-size: 1.4rem;
        }
    }
</style>

@section('content')
<div class="row g-3 g-xl-4 review-wrapper">
    <section class="col-12">
        <div class="glass-card pane h-100">
            <h5 class="mb-3">Review Your Order</h5>

            <!-- ✅ Product Summary -->
            <div class="mb-3">
                <h6>Products</h6>
                <ul id="reviewCartItems" class="list-group small"></ul>
            </div>

            <!-- ✅ Delivery Details -->
            <div class="mb-3">
                <h6>Delivery Details</h6>
                <ul class="list-group small" id="deliverySummary">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Name:</span> <span id="reviewName">—</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Order Type:</span> <span id="reviewOrderType">—</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between recurringField">
                        <span>Frequency:</span> <span id="reviewFrequency">—</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Delivery Date:</span> <span id="reviewDeliveryDate">—</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between recurringField">
                        <span>Start:</span> <span id="reviewStartMonth">—</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between recurringField">
                        <span>End:</span> <span id="reviewEndMonth">—</span>
                    </li>
                </ul>
            </div>

            <div class="d-flex justify-content-between fw-bold my-3">
                <span>Total:</span>
                <span id="reviewTotal">KSh 0</span>
            </div>

            <form id="submitOrderForm" method="POST" action="{{ route('biller.customer_pages.submit_order') }}">
                @csrf
                <input type="hidden" name="order_payload" id="orderPayload">

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-light" id="btnBack">← Back</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitOrder">Submit Order →</button>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- ✅ Bottom Nav -->
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
        <a class="nav-pill" href="{{ route('biller.logout') }}">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </div>
</div>
@endsection

@section('extra-scripts')
<script>
$(function() {
    let cart = JSON.parse(localStorage.getItem("cartItems") || "{}");
    let customer = JSON.parse(localStorage.getItem("customerDetails") || "{}");

    let total = 0;
    let cartHtml = "";

    // Build cart summary
    $.each(cart, function(id, item) {
        let line = item.qty * item.price;
        total += line;
        cartHtml += `
            <li class="list-group-item d-flex justify-content-between">
                <span>${item.name} (x${item.qty})</span>
                <strong>KSh ${line.toLocaleString()}</strong>
            </li>`;
    });

    $("#reviewCartItems").html(cartHtml);
    $("#reviewTotal").text("KSh " + total.toLocaleString());

    // Fill in delivery details
    $("#reviewName").text(customer.name || "—");
    $("#reviewOrderType").text(customer.order_type || "—");
    $("#reviewFrequency").text(customer.frequency || "—");
    $("#reviewDeliveryDate").text(customer.delivery_date || "—");
    $("#reviewStartMonth").text(customer.start_month || "—");
    $("#reviewEndMonth").text(customer.end_month || "—");

    // Delivery Days + Week Numbers
    let daysList = (customer.delivery_days && customer.delivery_days.length)
        ? customer.delivery_days.join(", ")
        : "—";

    let weekList = (customer.week_numbers && customer.week_numbers.length)
        ? customer.week_numbers.join(", ")
        : "—";

    let extraInfo = "";

    if (customer.frequency === "daily") {
        extraInfo = `<li class="list-group-item d-flex justify-content-between">
                        <span>Delivery Days:</span><span>All Days (Mon–Sun)</span>
                     </li>`;
    } else if (customer.frequency === "weekly") {
        extraInfo = `<li class="list-group-item d-flex justify-content-between">
                        <span>Delivery Days:</span><span>${daysList}</span>
                     </li>`;
    } else if (customer.frequency === "custom") {
        extraInfo = `
            <li class="list-group-item d-flex justify-content-between">
                <span>Delivery Days:</span><span>${daysList}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span>Week Numbers:</span><span>${weekList}</span>
            </li>`;
    }

    $("#reviewFrequency").closest('li').after(extraInfo);

    // ✅ Show Locations per Day (new section)
    if (customer.locations_for_days && Object.keys(customer.locations_for_days).length > 0) {
        let locationInfo = "<li class='list-group-item'><strong>Day-to-Location Mapping:</strong><br><ul class='ps-3 small mb-0'>";
        $.each(customer.locations_for_days, function(dayKey, locs) {
            if (locs.length > 0) {
                locationInfo += `<li>${capitalize(dayKey.replace(/_/g, ' '))}: <span class="text-muted">${locs.join(', ')}</span></li>`;
            }
        });
        locationInfo += "</ul></li>";
        $("#deliverySummary").append(locationInfo);
    }

    // Hide recurring-only fields for one-time orders
    if (customer.order_type !== "recurring") {
        $(".recurringField").hide();
    }

    // Optional: Preview short delivery schedule summary
    if (customer.order_type === "recurring") {
        let summary = "<ul class='list-group small mt-2'>";
        summary += `<li class="list-group-item text-muted">Schedule will start from 
                    <strong>${customer.start_month}</strong> 
                    to <strong>${customer.end_month}</strong>.</li>`;
        summary += "</ul>";
        $("#reviewEndMonth").closest('li').after(summary);
    }

    // ✅ Final payload for backend
    $("#orderPayload").val(JSON.stringify({
        customer: customer,
        cart: cart,
        total: total
    }));

    // Back button
    $("#btnBack").click(() => {
        window.location.href = "{{ route('biller.customer_pages.delivery') }}";
    });

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
});
</script>
@endsection
