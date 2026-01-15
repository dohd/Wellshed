@extends('core.layouts.apps')
@section('title', 'Payments')

@section('content')
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
<div class="page-wrapper">
    {{-- ✅ Wallet Summary --}}
    <div class="card-box text-center">
        <p class="text-muted mb-1">Wallet Balance</p>
        <h2 class="wallet-balance">KSh {{ numberFormat($balance) }}</h2>

        <button type="button" class="btn w-100 mt-3 add-payment-btn text-white" data-toggle="modal" data-target="#mpesaModal">
            + Add Payment
        </button>
    </div>

    {{-- ✅ Recent Payments --}}
    <h4 class="section-title">Recent Transactions</h4>

    <ul class="list-group">
        @foreach ($receipts as $receipt)
            @if ($receipt->payment_for === 'subscription')
                <li class="list-group-item d-flex justify-content-between">
                    <span>Subscription {{ gen4tid('#', $receipt->tid) }}</span>
                    <span class="text-success">+ KSh {{ numberFormat($receipt->amount) }}</span>
                    <small class="text-muted">{{ date('M d, Y', strtotime($receipt->date)) }}</small>
                </li>
            @elseif ($receipt->payment_for === 'order')    
                <li class="list-group-item d-flex justify-content-between">
                    <span>Order <span style="margin-left: 40px;">&nbsp;</span> {{ gen4tid('#', $receipt->tid) }}</span>
                    <span class="text-success">+ KSh {{ numberFormat($receipt->amount) }}</span>
                    <small class="text-muted">{{ date('M d, Y', strtotime($receipt->date)) }}</small>
                </li>
            @elseif ($receipt->payment_for === 'charge' && $receipt->credit > 0)    
                <li class="list-group-item d-flex justify-content-between">
                    <span>Top-up <span style="margin-left: 40px;">&nbsp;</span> {{ gen4tid('#', $receipt->tid) }}</span>
                    <span class="text-success">+ KSh {{ numberFormat($receipt->amount) }}</span>
                    <small class="text-muted">{{ date('M d, Y', strtotime($receipt->date)) }}</small>
                </li>
            @elseif ($receipt->debit > 0)
                <li class="list-group-item d-flex justify-content-between">
                    <span>Debit Charge <span style="margin-left: 10px;">&nbsp;</span> {{ gen4tid('#', $receipt->tid) }}</span>
                    <span class="text-danger">- KSh {{ numberFormat($receipt->amount) }}</span>
                    <small class="text-muted">{{ date('M d, Y', strtotime($receipt->date)) }}</small>
                </li>
            @endif
        @endforeach
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
@include('focus.pages.modals.mpesa_modal')
@endsection

@section('after-scripts')
<script>
$(function() {
    const customer = @json($customer);

    $.ajaxSetup({ 
      headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } 
    });

    // ==== Handle payment for change =====
    $('#mpesaPaymentFor').change(function() {
        $option = $(this).find(':selected');
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

    $('#serviceFee').keyup(function() {
        const value = accounting.unformat(this.value);
        const amount = accounting.unformat($('#mpesaPaymentFor :selected').data('price'));
        const total = value + amount;
         $('#mpesaAmount').val(total);
    });
    $('#serviceFee').keyup();

    // ==== Handle form submit ====
    $('#mpesaPromptForm').on('submit', function(e){
        e.preventDefault();
        // Simple validation
        const phone = $('#mpesaPhone').val().trim();
        const amount = $('#mpesaAmount').val().trim();
        const notes = $('#mpesaNotes').val().trim() || null;
        if(!phone || !amount || !notes) { 
            return alert('Please fill all required fields.'); 
        }
        const customerName = customer.company || customer.name;
        if (!customerName) return alert('Customer name or company required');

        $('#mpesaStatusArea').removeClass('d-none');
        $('#btnSendMpesa').prop('disabled', true);

        // AJAX stub
        $.ajax({
            url: "{{ route('api.mpesa_stkpush') }}",
            method: 'POST',
            data: {
                phone,
                amount,
                account_reference: customerName,
                description: notes,
                ins: customer.ins,
            },
            success: function(res) {
                if (res.ok && res.status === "PENDING") {
                    $('#mpesaStatusArea .alert')
                      .removeClass('alert-info')
                      .addClass('alert-success')
                      .html('<i class="fas fa-check-circle mr-2"></i> Prompt sent ✅ — Enter M-Pesa PIN');
                      setTimeout(() => {
                        $('#mpesaModal').modal('hide');
                        $('#btnSendMpesa').prop('disabled', false);
                        startPolling();
                      }, 2500);                
                } else {
                    const errorMsg = "Error: " + (res.gateway?.ResponseDescription || res.message);
                    $('#mpesaStatusArea .alert')
                      .removeClass('alert-info')
                      .addClass('alert-danger')
                      .html(`<i class="fas fa-check-circle mr-2"></i> ${errorMsg}`);
                }            
            },
            error: function() {
                $('#mpesaStatusArea .alert')
                  .removeClass('alert-info')
                  .addClass('alert-danger')
                  .html('<i class="fas fa-times-circle mr-2"></i> Failed to send prompt. Please retry.');
                  $('#btnSendMpesa').prop('disabled', false);
            }
        });

        function testStub() {
            $('#mpesaStatusArea .alert')
                .removeClass('alert-info')
                .addClass('alert-success')
                .html('<i class="fas fa-check-circle mr-2"></i> Prompt sent successfully. Ask customer to complete on phone.');
            setTimeout(() => {
                $('#mpesaModal').modal('hide');
                $('#btnSendMpesa').prop('disabled', false);
                postPaymentLocally({merchant_request_id: 1234567, checkout_request_id: 1234567});
            }, 2500);   
        }
        
        // Test
        @if (env('APP_ENV') === 'local' && env('APP_DEBUG') === true)
            testStub();
        @endif 
    });


    /** ✅ Step 2 — Poll status until SUCCESS */
    function startPolling() {
        $('#mpesaStatusArea .alert').html('<i class="fas fa-check-circle mr-2"></i> Awaiting confirmation...');
        pollTimer = setInterval(function() {
            $.get(`/api/mpesa_payment/${checkoutID}`, function(res) {
                if (!res.ok) {
                    clearInterval(pollTimer);
                    return;
                }
                let status = res.status;
                $('#mpesaStatusArea .alert').html(`<i class="fas fa-check-circle mr-2"></i> ${status}`);
                if (status === "SUCCESS") {
                    clearInterval(pollTimer);
                    $('#mpesaStatusArea .alert').html(`<i class="fas fa-check-circle mr-2"></i> Payment Confirmed ✅`);
                    setTimeout(() => postPaymentLocally(res.data), 600);
                }
                if (status === "FAILED" || status === "CANCELLED") {
                    clearInterval(pollTimer);
                    $('#mpesaStatusArea .alert')
                      .removeClass('alert-info')
                      .addClass('alert-danger')
                      .html(`<i class="fas fa-times-circle mr-2"></i> Payment ${status} ❌`);
                }
            });
        }, 3500);
    }

  // ==== Locally POST payment ====
  function postPaymentLocally(data) {
    $.ajax({
      url: "{{ route('biller.payment_receipts.store') }}",
      method: 'POST',
      data: {
        'entry_type': 'receive',
        'customer_id': customer.id,
        'amount': $('#mpesaAmount').val().trim(),
        'date': "{{ date('Y-m-d') }}",
        'payment_method': 'mpesa', 
        'payment_for': $('#mpesaPaymentFor').val().trim(),
        'notes': $('#mpesaNotes').val().trim(),
        'subscription_id': $('#subscrId').val(),
        'charge_id': $('#chargeId').val(),
        'merchant_request_id': data.merchant_request_id,
        'checkout_request_id': data.checkout_request_id,
        'refs': {
            mpesa_phone: $('#mpesaPhone').val().trim(),
            mpesa: data.mpesa_receipt_number,
        },
      },
      success: function(res){
        if (res.message) {
            alert(res.message);
            setTimeout(() => location.reload(), 1500);
        }
      },
      error: function(xhr, status, error){
        const {message} = xhr?.responseJSON;
        if (message) alert(message);
      }
    });
  } 

  // ==== Auto-reset modal each time it closes ====
  $('#mpesaModal').on('hidden.bs.modal', function(){
    $('#mpesaPromptForm')[0].reset();
    $('#mpesaStatusArea').addClass('d-none')
      .find('.alert')
      .removeClass('alert-success alert-danger')
      .addClass('alert-info')
      .html('<i class="fas fa-spinner fa-spin mr-2"></i> Sending prompt...');
    $('#btnSendMpesa').prop('disabled', false);
  });
});
</script>
@endsection