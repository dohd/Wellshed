<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Details</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand: #1f6fd9;
            --soft: #f5f8ff;
            --ink: #0f172a;
        }

        body {
            background: var(--soft);
            color: #111827;
        }

        .app {
            max-width: 1200px;
            margin-inline: auto;
            padding: 24px 16px;
        }

        .glass-card {
            background: #fff;
            border: 1px solid #e9eef7;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(17, 24, 39, .04);
        }

        .pane {
            padding: 22px;
        }

        .title {
            font-weight: 700;
            color: var(--ink);
        }

        .cta-btn {
            background: var(--brand);
            border: 0;
        }

        .cta-btn:hover {
            filter: brightness(.95);
        }
    </style>
    <style>
        body {
            background: #f8f9fa;
        }

        .subscription-card {
            max-width: 600px;
            margin: 25px auto;
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .subscription-header h3 {
            font-weight: 600;
        }

        .badge-status {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        @media (max-width: 576px) {
            .subscription-card {
                margin: 15px;
                padding: 18px;
            }
        }
    </style>
</head>

<body>

    <div class="subscription-card">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Subscription Details</h3>

            @if ($subscription->status == 'expired')
                <span class="badge bg-danger badge-status">Expired</span>
            @else
                <span class="badge bg-success badge-status">Active</span>
            @endif
        </div>

        <!-- Details -->
        <div class="mb-2">
            <strong>Customer:</strong><br>
            <span>{{ $customer->name }}</span>
        </div>

        <div class="mb-2">
            <strong>Package:</strong><br>
            <span>{{ @$subscription->package->name }}</span>
        </div>

        <div class="mb-2">
            <strong>Start Date:</strong><br>
            <span>{{ $subscription->start_date }}</span>
        </div>

        <div class="mb-2">
            <strong>End Date:</strong><br>
            <span>{{ $subscription->end_date }}</span>
        </div>

        <div class="mb-2">
            <strong>Price:</strong><br>
            <span>KSH {{ number_format($subscription->package->price, 2) }}</span>
        </div>

        <!-- Status Message -->
        @if ($subscription->status == 'expired')
            <div class="alert alert-danger mt-3">
                Your subscription has expired. Please renew to restore service.
            </div>
        @else
            <div class="alert alert-warning mt-3">
                Your subscription will expire on {{ $subscription->end_date }}.
            </div>
        @endif

        <!-- Pay Button -->
        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                Pay Now
            </button>
        </div>
    </div>


    <!-- ========================================= -->
    <!-- PAYMENT MODAL -->
    <!-- ========================================= -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm modal-mobile-full">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Complete Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- <div class="modal-body">

                <p>Amount: <strong>KSH {{ number_format($subscription->package->price, 2) }}</strong></p>
                <p>Package: <strong>{{ $subscription->package->name }}</strong></p>

                <hr>

                <p class="text-muted">Click below to proceed with payment.</p>

                <a href="{{ route('crm.subscription.pay', $subscription->id) }}"
                   class="btn btn-success w-100">
                    Proceed to Payment
                </a>

            </div> --}}
                <form id="mpesaPromptForm">
                    <div class="modal-body">
                        <p class="mb-1 text-muted">Enter the customer's phone number to initiate an M-Pesa STK Push.</p>
                        <div class="form-group mb-2">
                            <label for="mpesaPhonePrompt" class="font-weight-semibold">Phone Number (Safaricom) <span
                                    class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="mpesaPhone" name="mpesa_phone"
                                placeholder="2547XXXXXXXX" required>
                        </div>

                        <div class="form-group mb-2">
                            <label for="mpesaPaymentFor" class="font-weight-semibold">Payment For <span
                                    class="text-danger">*</span></label>
                            <input type="hidden" id="subscrId">
                            <input type="hidden" id="chargeId">
                            <select id="mpesaPaymentFor" class="form-control">
                                @if ($subscrPlan)
                                    {{-- <option value="subscription" data-name="{{ $subscrPlan->name }} Plan" data-id="{{ $subscription->id }}" data-price="{{ +$subscrPlan->price }}">
                        {{ $subscrPlan->name }} Plan (KES {{ numberFormat($subscrPlan->price) }} / month)
                      </option> --}}
                                @endif
                                @foreach ($charges as $charge)
                                    <option value="charge" data-name="{{ $charge->notes ?? 'Debit Charge' }}"
                                        data-id="{{ $charge->id }}" data-amount="{{ $charge->amount }}">
                                        {{ $charge->tid }} - {{ $charge->notes }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if (!$isRecur)
                            <div class="form-group mb-2">
                                <label for="serviceFee" class="font-weight-semibold">One-time Service Fee <span
                                        class="text-danger">*</span></label>
                                <input type="number" min="1" step="1" class="form-control" id="serviceFee"
                                    name="serviceFee" value="{{ $subscrPlan->onetime_fee }}" readonly>
                            </div>
                        @endif

                        <div class="form-group mb-2">
                            <label for="mpesaAmountPrompt" class="font-weight-semibold">Amount (KES) <span
                                    class="text-danger">*</span></label>
                            <input type="number" min="1" step="1" class="form-control" id="mpesaAmount"
                                name="amount" placeholder="e.g. 500" readonly required>
                        </div>

                        <div class="form-group mb-2">
                            <label for="notes" class="font-weight-semibold">Notes <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mpesaNotes" name="notes"
                                placeholder="e.g. Pay for ORD-1001">
                        </div>

                        <div id="mpesaStatusArea" class="mt-3 d-none">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Sending prompt to the customer’s phone...
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btnSendMpesa">
                            <i class="fas fa-paper-plane mr-1"></i> Send Prompt
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Popper -->
    {{ Html::script(mix('js/app_end.js')) }}
    {{ Html::script('focus/js/control.js?b=' . config('version.build')) }}
    {{ Html::script('focus/js/custom.js?b=' . config('version.build')) }}
    @yield('after-scripts')
    @yield('extra-scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(function () {

        const customer = @json($customer);

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        });

        // =========================================================
        // CHANGE PAYMENT FOR (Subscriptions / Charges)
        // =========================================================
        $('#mpesaPaymentFor').change(function () {
            const $option = $(this).find(':selected');
            const name = $option.data('name');

            $('#mpesaAmount, #chargeId, #subscrId').val('');
            $('#mpesaNotes').val(name);

            if ($(this).val() === 'subscription') {
                const price = accounting.unformat($option.data('price'));
                const subscrId = accounting.unformat($option.data('id'));
                $('#subscrId').val(subscrId);
                $('#mpesaAmount').val(price);
                $('#mpesaNotes').attr('placeholder', 'e.g Subscription');
            } else if ($(this).val() === 'charge') {
                const amount = accounting.unformat($option.data('amount'));
                const chargeId = accounting.unformat($option.data('id'));
                $('#chargeId').val(chargeId);
                $('#mpesaAmount').val(amount);
                $('#mpesaNotes').attr('placeholder', 'e.g Charge');
            }
        });
        $('#mpesaPaymentFor').change();

        // Auto include service fee (non recurring)
        $('#serviceFee').keyup(function () {
            const value = accounting.unformat(this.value);
            const amount = accounting.unformat($('#mpesaPaymentFor :selected').data('price'));
            $('#mpesaAmount').val(value + amount);
        });
        $('#serviceFee').keyup();

        // =========================================================
        // SUBMIT — SEND STK PUSH
        // =========================================================
        $('#mpesaPromptForm').on('submit', function (e) {
            e.preventDefault();

            const phone = $('#mpesaPhone').val().trim();
            const amount = $('#mpesaAmount').val().trim();
            const notes  = $('#mpesaNotes').val().trim();
            const customerName = customer.company || customer.name;

            if (!phone || !amount || !notes) return alert("Fill all required fields.");

            $('#mpesaStatusArea').removeClass('d-none');
            $('#btnSendMpesa').prop('disabled', true);

            let pollTimer = null;
            let checkoutID = null;

            // helper to stop polling safely & idempotently
            function stopPolling() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                    console.log('MPESA polling stopped.');
                }
            }

            // If user clicks Cancel (or any element inside modal that dismisses it), stop polling immediately
            // This binds to any element inside the modal with data-bs-dismiss="modal" (your Cancel button uses that attribute).
            $('#paymentModal').find('[data-bs-dismiss="modal"]').off('click.stopPolling').on('click.stopPolling', function () {
                stopPolling();
                $('#mpesaStatusArea .alert')
                    .removeClass('alert-info alert-warning alert-success')
                    .addClass('alert-danger')
                    .html('<i class="fas fa-times-circle"></i> Payment cancelled by user.');
                $('#btnSendMpesa').prop('disabled', false);
            });

            // Also stop polling if modal is closed by X/backdrop/ESC
            $('#paymentModal').off('hidden.bs.modal.stopPolling').on('hidden.bs.modal.stopPolling', function () {
                stopPolling();
                $('#btnSendMpesa').prop('disabled', false);
            });

            // --------------------------------------------------
            // STEP 1: SEND STK PUSH
            // --------------------------------------------------
            $.ajax({
                url: "{{ route('api.mpesa_stkpush') }}",
                method: "POST",
                data: {
                    phone,
                    amount,
                    account_reference: customerName,
                    description: notes,
                    ins: customer.ins,
                },
                success: function (res) {
                    console.log("STK Response:", res);

                    if (res.ok && res.status === "PENDING") {
                        checkoutID = res.checkout_request_id;

                        $('#mpesaStatusArea .alert')
                            .removeClass('alert-info')
                            .addClass('alert-warning')
                            .html("<b>STK sent</b> — Ask the customer to enter M-Pesa PIN...");

                        startPolling(); // ⭐ Start polling here
                    } else {
                        $('#mpesaStatusArea .alert')
                            .removeClass('alert-info')
                            .addClass('alert-danger')
                            .html("Error: " + (res.gateway?.ResponseDescription || res.message));

                        $('#btnSendMpesa').prop('disabled', false);
                    }
                },
                error: function () {
                    $('#mpesaStatusArea .alert')
                        .removeClass('alert-info')
                        .addClass('alert-danger')
                        .html("Failed to send STK push. Try again.");

                    $('#btnSendMpesa').prop('disabled', false);
                }
            });

            // --------------------------------------------------
            // STEP 2: POLLING FOR MPESA RESULT
            // --------------------------------------------------
            function startPolling() {

                $('#mpesaStatusArea .alert')
                    .removeClass('alert-warning')
                    .addClass('alert-info')
                    .html("Waiting for M-Pesa confirmation…");

                pollTimer = setInterval(() => {

                    $.get(`/api/mpesa_payment/${checkoutID}`, function (res) {

                        console.log("Polling:", res);

                        if (!res.ok) return;

                        let status = res.status;

                        // Still pending
                        if (status === "PENDING") {
                            $('#mpesaStatusArea .alert').html("Waiting for payment...");
                            return;
                        }

                        // SUCCESS
                        if (status === "SUCCESS") {
                            stopPolling();

                            $('#mpesaStatusArea .alert')
                                .removeClass('alert-info')
                                .addClass('alert-success')
                                .html("<b>Payment confirmed ✓</b>");

                            // Save into your DB
                            setTimeout(() => {
                                // hide modal politely
                                const modalEl = document.getElementById('paymentModal');
                                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                modalInstance.hide();

                                postPaymentLocally(res.data);
                            }, 800);

                            return;
                        }

                        // FAIL / CANCEL
                        if (status === "FAILED" || status === "CANCELLED") {
                            stopPolling();

                            $('#mpesaStatusArea .alert')
                                .removeClass('alert-info')
                                .addClass('alert-danger')
                                .html(`Payment ${status} ❌`);

                            $('#btnSendMpesa').prop('disabled', false);
                            return;
                        }
                    });

                }, 3500);
            }

            // Stop polling if modal closed before completion (extra guard - already wired above)
            $('#paymentModal').on('hidden.bs.modal', function () {
                if (pollTimer) clearInterval(pollTimer);
                $('#btnSendMpesa').prop('disabled', false);
            });
        });

        // =========================================================
        // STEP 3: SAVE PAYMENT LOCALLY (ONLY IF SUCCESSFUL)
        // =========================================================
        function postPaymentLocally(data) {
            $.ajax({
                url: "{{ route('biller.payment_receipts.store') }}",
                method: 'POST',
                data: {
                    entry_type: 'receive',
                    customer_id: customer.id,
                    amount: $('#mpesaAmount').val().trim(),
                    date: "{{ date('Y-m-d') }}",
                    payment_method: 'mpesa',
                    payment_for: $('#mpesaPaymentFor').val().trim(),
                    notes: $('#mpesaNotes').val().trim(),
                    subscription_id: $('#subscrId').val(),
                    charge_id: $('#chargeId').val(),
                    merchant_request_id: data.merchant_request_id,
                    checkout_request_id: data.checkout_request_id,
                    refs: {
                        mpesa_phone: $('#mpesaPhone').val().trim(),
                    },
                },
                success: function (res) {
                    alert(res.message);
                    location.reload();
                },
                error: function (xhr) {
                    alert(xhr?.responseJSON?.message ?? "Failed to save payment.");
                }
            });
        }

        // =========================================================
        // RESET MODAL
        // =========================================================
        $('#paymentModal').on('hidden.bs.modal', function () {
            $('#mpesaPromptForm')[0].reset();
            $('#mpesaStatusArea')
                .addClass('d-none')
                .find('.alert')
                .removeClass('alert-success alert-danger')
                .addClass('alert-info')
                .html('<i class="fas fa-spinner fa-spin mr-2"></i> Sending prompt...');
            $('#btnSendMpesa').prop('disabled', false);
        });

    });
    </script>


</body>

</html>
