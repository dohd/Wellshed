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

                <div class="mb-3">
                    <h6>Products</h6>
                    <ul id="reviewCartItems" class="list-group small"></ul>
                </div>

                <div class="mb-3">
                    <input type="hidden" value="{{ $customer->ins }}" id="ins">
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
                    <input type="hidden" name="payment_id" id="paymentId"><!-- ✅ new -->

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

    <!-- ✅ M-PESA MODAL -->
    <div class="modal fade" id="mpesaModal" tabindex="-1" aria-labelledby="mpesaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mpesaModalLabel">M-Pesa Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Enter your MPESA phone number:</p>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" id="mpesaPhone" class="form-control" placeholder="07XXXXXXXX">
                    </div>

                    <div id="mpesaStatus" class="text-center small text-muted"></div>
                </div>

                <div class="modal-footer">
                    <button id="mpesaPayBtn" class="btn btn-success">Pay Now</button>
                </div>
            </div>
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
    $("#reviewName").text(customer.name || "—");
    $("#reviewOrderType").text(customer.order_type || "—");
    $("#reviewFrequency").text(customer.frequency || "—");
    $("#reviewDeliveryDate").text(customer.delivery_date || "—");
    $("#reviewStartMonth").text(customer.start_month || "—");
    $("#reviewEndMonth").text(customer.end_month || "—");

    if (customer.order_type !== "recurring") {
        $(".recurringField").hide();
    }

    $("#orderPayload").val(JSON.stringify({
        customer: customer,
        cart: cart,
        total: total
    }));

    $("#btnBack").click(() => {
        window.location.href = "{{ route('biller.customer_pages.delivery') }}";
    });

    /* ✅ ONE-TIME ORDER → MPESA PAYMENT FLOW */
    if (customer.order_type === "one_time") {

        let checkoutID = null;
        let pollTimer = null;

        $("#btnSubmitOrder").text("Pay with M-Pesa →");

        $("#btnSubmitOrder").off("click").on("click", function(e) {
            e.preventDefault();
            new bootstrap.Modal(document.getElementById("mpesaModal")).show();
        });

        /** ✅ Step 1 — Start STK Push */
        $("#mpesaPayBtn").on("click", function() {
            let phone = $("#mpesaPhone").val();
            let ins = $("#ins").val();
            if (!phone) {
                alert("Enter phone number");
                return;
            }
            $("#mpesaStatus").text("Sending STK push…");
            $.ajax({
                url: "{{ route('api.mpesa_stkpush') }}",
                method: "POST",
                data: {
                    phone: phone,
                    // amount: 1,
                    amount: total,
                    account_reference: customer.name,
                    description: "Order Payment",
                    ins: ins,
                },
                success: function (res) {
                    console.log("STK Response:", res);
                    if (res.ok && res.status === "PENDING") {
                        checkoutID = res.checkout_request_id;
                        $("#mpesaStatus").text("STK sent ✅ — Enter M-Pesa PIN");
                        startPolling();
                    } else {
                        $("#mpesaStatus").text("Error: " + (res.gateway?.ResponseDescription || res.message));
                    }
                },
                error: function(xhr) {
                    $("#mpesaStatus").text("Server error");
                }
            });
        });

        /** ✅ Step 2 — Poll status until SUCCESS */
        function startPolling() {
            $("#mpesaStatus").text("Waiting for confirmation…");
            pollTimer = setInterval(function() {
                $.get(`/api/mpesa_payment/${checkoutID}`, function(res) {
                    console.log("Polling:", res);
                    if (!res.ok) return;
                    let status = res.status;
                    $("#mpesaStatus").text("Status: " + status);
                    if (status === "SUCCESS") {
                        clearInterval(pollTimer);
                        $("#mpesaStatus").html(`<span class='text-success fw-bold'>Payment Confirmed ✅</span>`);
                        setTimeout(() => {
                            postPaymentLocally(res.data);
                        }, 600);
                    }
                    if (status === "FAILED" || status === "CANCELLED") {
                        clearInterval(pollTimer);
                        $("#mpesaStatus").html(`<span class='text-danger fw-bold'>Payment ${status} ❌</span>`);
                    }
                });
            }, 3500);
        }

        /** ✅ Step 3 — Save payment locally then submit order */
        function postPaymentLocally(data) {
            $.ajax({
                url: "{{ route('biller.payment_receipts.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    entry_type: 'receive',
                    customer_id: customer.customer_id,
                    amount: data.amount,                   // ✅ from callback
                    date: "{{ date('Y-m-d') }}",
                    payment_method: 'mpesa',
                    payment_for: "order",
                    notes: `Mpesa Receipt ${data.mpesa_receipt_number}`,
                    merchant_request_id: data.merchant_request_id,
                    checkout_request_id: data.checkout_request_id,
                    refs: {
                        mpesa_phone: data.phone
                    },
                },
                success: function (res) {
                    console.log("Local save:", res);
                    const paymentId =
                        res?.payment?.id ||
                        null;
                    if (paymentId) {
                        $("#paymentId").val(paymentId);   // ✅ attach to the form
                    }
                    // ✅ Submit order only AFTER storing receipt
                    $("#submitOrderForm").submit();
                },
                error: function(xhr) {
                    console.log(xhr.responseJSON);
                    alert("Failed to save local MPESA receipt");
                }
            });
        }
    }

});
</script>
@endsection

