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
        <h2 class="wallet-balance">KSh {{ numberFormat($balance) }}</h2>

        <button type="button" class="btn w-100 mt-3 add-payment-btn" data-toggle="modal" data-target="#mpesaModal">
            + Add Payment
        </button>
    </div>

    {{-- ✅ Recent Payments --}}
    <h4 class="section-title">Recent Transactions</h4>

    <ul class="list-group">
        @foreach ($receipts as $receipt)
            @if ($receipt->subscription)
                <li class="list-group-item d-flex justify-content-between">
                    <span>Subscription {{ gen4tid('#', $receipt->tid) }}</span>
                    <span class="text-success">+ KSh {{ numberFormat($receipt->amount) }}</span>
                    <small class="text-muted">{{ date('M d, Y', strtotime($receipt->date)) }}</small>
                </li>
            @elseif ($receipt->order)    
                <li class="list-group-item d-flex justify-content-between">
                    <span>Order <span style="margin-left: 40px;">&nbsp;</span> {{ gen4tid('#', $receipt->tid) }}</span>
                    <span class="text-success">+ KSh {{ numberFormat($receipt->amount) }}</span>
                    <small class="text-muted">{{ date('M d, Y', strtotime($receipt->date)) }}</small>
                </li>
            @elseif ($receipt->charge_id)    
                <li class="list-group-item d-flex justify-content-between">
                    <span>Charge <span style="margin-left: 40px;">&nbsp;</span> {{ gen4tid('#', $receipt->tid) }}</span>
                    <span class="text-success">+ KSh {{ numberFormat($receipt->amount) }}</span>
                    <small class="text-muted">{{ date('M d, Y', strtotime($receipt->date)) }}</small>
                </li>
            @elseif ($receipt->debit > 0)
                <li class="list-group-item d-flex justify-content-between">
                    <span>Charge Debit {{ gen4tid('#', $receipt->tid) }}</span>
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
$(function(){
  // ==== Handle form submit ====
  $('#mpesaPromptForm').on('submit', function(e){
    e.preventDefault();

    // Simple validation
    const phone = $('#mpesaPhonePrompt').val().trim();
    const amount = $('#mpesaAmountPrompt').val().trim();
    if(!phone || !amount){ alert('Please fill all required fields.'); return; }

    $('#mpesaStatusArea').removeClass('d-none');
    $('#btnSendMpesa').prop('disabled', true);

    console.log({
        phone: phone,
        amount: amount,
        reference: $('#mpesaOrderRef').val()
    });

    // Example AJAX stub
    {{-- $.ajax({
      url: '/api/payments/mpesa/stkpush',
      method: 'POST',
      data: {
        phone: phone,
        amount: amount,
        reference: $('#mpesaOrderRef').val()
      },
      success: function(res){
        $('#mpesaStatusArea .alert')
          .removeClass('alert-info')
          .addClass('alert-success')
          .html('<i class="fas fa-check-circle mr-2"></i> Prompt sent successfully. Ask customer to complete on phone.');
        setTimeout(()=>$('#mpesaPromptModal').modal('hide'), 2500);
      },
      error: function(){
        $('#mpesaStatusArea .alert')
          .removeClass('alert-info')
          .addClass('alert-danger')
          .html('<i class="fas fa-times-circle mr-2"></i> Failed to send prompt. Please retry.');
        $('#btnSendMpesa').prop('disabled', false);
      }
    }); --}}
  });

  // ==== Auto-reset modal each time it closes ====
  $('#mpesaPromptModal').on('hidden.bs.modal', function(){
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