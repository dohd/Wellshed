{{ Html::script('focus/js/select2.min.js') }}
<script>
  $(function(){
    // ==== Config ====
    $('#customer').select2({allowClear: true});
    $.ajaxSetup({ 
      headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } 
    });


    // ==== Helpers ====
    function fmtKES(v){ return 'KES ' + (Number(v||0)).toLocaleString('en-KE'); }
    function setToday($el){ $el.val(new Date().toISOString().slice(0,10)); }
    function currentEntry(){ return $('#entryTypeGroup input[name="entryTypeOpt"]:checked').val(); }
    function currentPaymentFor(){ return $('#paymentForGroup input[name="paymentForOpt"]:checked').val(); }
    function quickToast(){ $('#saveToast').toast('show'); }
    function focusFirst(){
      if ($('#customer:enabled').val() === '') return $('#customer').focus();
      if ($('#subscriptionSection').is(':visible')) return $('#plan').focus();
      if ($('#orderSection').is(':visible')) return $('#order').focus();
      if ($('#chargeReceiveSection').is(':visible')) return $('#charge').focus();
      if ($('#debitSection').is(':visible')) return $('#debitReason').focus();
      return $('#amount').focus();
    }

    // Prevent accidental submit on Enter
    $(document).on('keydown', function(e){
      if (e.key === 'Enter' && !$('.modal.show').length && !$('.dropdown-menu.show').length){
        e.preventDefault(); $('#entryForm').submit();
      }
    });

    // ==== Cache ====
    var $form = $('#entryForm');
    var $customer = $('#customer');
    var $summaryCustomer = $('#summaryCustomer'),
        $summaryType = $('#summaryType'),
        $summaryFor = $('#summaryFor'),
        $summaryDetails = $('#summaryDetails'),
        $summaryDate = $('#summaryDate'),
        $summaryAmount = $('#summaryAmount'),
        $summaryMethod = $('#summaryMethod');

    var $paymentForWrap = $('#paymentForWrap');
    var $subscriptionSection = $('#subscriptionSection');
    var $orderSection = $('#orderSection');
    var $chargeReceiveSection = $('#chargeReceiveSection');
    var $debitSection = $('#debitSection');

    // Subscription
    var $plan = $('#plan');
    // Charge Receive
    var $chargeRef = $('#chargeRef'), $chargeOutstanding = $('#chargeOutstanding'), $applyAmount = $('#applyAmount');
    var $applyHint = $('#applyHint'), $applyNow = $('#applyNow'), $applyRefShow = $('#applyRefShow');
    // Debit (lean)
    var $debitReason = $('#debitReason'), $debitDue = $('#debitDue');
    // Payment method & refs
    var $mpesaFields = $('#mpesaFields'), $mpesaRef = $('#mpesaRef');
    // Amount
    var $amount = $('#amount');

    // Defaults
    $('#subscrDate, #orderDate, #chargeDate, #debitDue').each(function() {
      setToday($(this));
    })
    $('#pmMpesa').prop('checked', true).trigger('change');

    // Payment method toggle
    $('input[name="paymentMethod"]').on('change', function(){
      var m = $(this).val();
      $mpesaFields.toggleClass('d-none', m !== 'mpesa');
      $mpesaRef.prop('required', m === 'mpesa');
      $summaryMethod.text(m === 'mpesa' ? 'M-Pesa' : 'Cash');
    });

    // Entry type toggle
    function toggleEntryTypeUI(){
      var t = currentEntry();
      if (t === 'debit') {
        $debitSection.removeClass('d-none');
        $debitReason.prop('required', true); $debitDue.prop('required', true);
        $('#paymentMethodBlock').addClass('d-none');
        $('input[name="paymentMethod"]').prop('checked', false);
        $mpesaRef.prop('required', false);
        $summaryMethod.text('—');
        $('#entryHint').text('You’re creating a customer debit (Dr A/R, Cr Revenue/Charges).');

        $paymentForWrap.hide();
        $subscriptionSection.hide();
        $orderSection.hide();
        $chargeReceiveSection.hide();
      } else {
        $debitSection.addClass('d-none');
        $debitReason.prop('required', false); $debitDue.prop('required', false);
        $('#paymentMethodBlock').removeClass('d-none');
        if (!$('input[name="paymentMethod"]:checked').length) $('#pmMpesa').prop('checked', true).trigger('change');
        $('#entryHint').text('You’re recording money received (Dr Cash/Bank, Cr A/R).');

        $paymentForWrap.show();
        togglePaymentForUI();
      }
      updateSummary();
      $('#primaryCta').text(t==='debit' ? 'Create Debit' : 'Record Payment');
      focusFirst();
    }

    // Payment For toggle
    function togglePaymentForUI(){
      var pf = currentPaymentFor();
      if (pf === 'subscription'){
        $subscriptionSection.show();
        $orderSection.hide();
        $chargeReceiveSection.hide();
        $('#contextHint').text('This receipt will be linked to a subscription.');
      } else if (pf === 'order'){
        $subscriptionSection.hide();
        $orderSection.show();
        $chargeReceiveSection.hide();
        $('#contextHint').text('This receipt will be linked to an order.');
      } else {
        $subscriptionSection.hide();
        $orderSection.hide();
        $chargeReceiveSection.show();
        $('#contextHint').text('This receipt will be applied to an existing charge.');
        syncApplyHint();
      }
      updateSummary();
      focusFirst();
    }
    $('#paymentForGroup input[name="paymentForOpt"]').on('change', function(){ 
      togglePaymentForUI(); 
      $amount.val('');
    });

    // Subscription: auto-amount from plan
    $plan.on('change', function(){
      var amt = $(this).find('option:selected').data('amount');
      if (amt) $amount.val(amt);
      updateSummary();
    });

    // Order change
    $('#order').on('change', function() {
      $option = $(this).find(':selected');
      const orderNo = $option.data('order_no');
      const total = $option.data('total');
      $('#orderNo').val(orderNo).change(); 
      $('#amount').val(total);
      updateSummary();
    });

    // Charge change
    $('#charge').on('change', function() {
      $option = $(this).find(':selected');
      const chargeRef = $option.data('order_no');
      $('#chargeRef').val(chargeRef).change(); 
      $amount.val('');
    });

    // Customer change
    $customer.on('change', function() {
      $('#phone').val('');
      $('#orderNo').val('').change();
      $('#plan option:not([value=""])').remove();
      $('#order option:not([value=""])').remove();
      $('#charge option:not([value=""])').remove();
      const customers = @json($customers);
      const customer = customers.filter(v => v.id == $(this).val())[0] || null;
      if (customer?.id) {
        // phone
        $('#phone').val(customer.phone);
        // subscriptions
        customer.subscriptions.forEach(({id, package}) => {
          if (package) {
            const price = accounting.formatNumber(package.price);
            const text = `${package.name} (KES ${price} / month)`;
            $('#plan').append(
              `<option value="${package.id}" data-subscription_id="${id}" data-amount="${package.price}" data-name="${package.name}" selected>
                ${text}
              </option>`
            );
            $('#plan').change();
          }
        });
        // orders
        customer.orders.forEach(({id, tid, total}) => {
          $('#order').append(`<option value="${id}" data-order_no="${tid}" data-total="${total}">${tid}</option>`);
        });
        // charges
        customer.charges.forEach(({id, tid, notes}) => {
          $('#charge').append(`<option value="${id}" data-charge_no="${tid}" data-notes="${notes}">${tid} ${notes? ' - ' : ''} ${notes}</option>`);
        });
      }
    });

    // Apply (Receive → Charge)
    function syncApplyHint(){
      if (currentEntry()==='receive' && currentPaymentFor()==='charge'){
        const amt = Number($amount.val()||0)||0;
        if (!Number($applyAmount.val())) $applyAmount.val(amt);
        const appl = Number($applyAmount.val()||0)||0;
        const ref = ($chargeRef.val()||'—');
        $applyNow.text(fmtKES(appl));
        $applyRefShow.text(ref);
        $applyHint.toggleClass('d-none', !(appl>0 && ref && ref!=='—'));
      } else {
        $applyHint.addClass('d-none');
      }
    }
    $amount.on('input', syncApplyHint);
    $applyAmount.on('input', syncApplyHint);
    $chargeRef.on('input', syncApplyHint);

    // Summary
    function updateSummary(){
      const custPhone = $customer.find('option:selected').data('phone');
      var custName = $customer.find('option:selected').data('name') || '—';
      var entry = currentEntry();
      var pf = currentPaymentFor();
      var amountVal = Number($amount.val()||0);

      let forTxt = '—', details = '—', dateTxt = '—', typeTxt = (entry==='debit' ? 'Charge Customer (Debit)' : 'Receive Payment');

      if (entry==='debit'){
        forTxt = 'Charge';
        details = ($('#debitReason').val() || 'Debit');
        dateTxt = $('#debitDue').val()||'—';
        $summaryMethod.text('—');
      } else {
        if (pf==='subscription'){
          forTxt = 'Subscription';
          const planName = $plan.find('option:selected').data('name') || '—';
          details = planName;
          dateTxt = $('#subscrDate').val()||'—';
        } else if (pf==='order'){
          forTxt = 'Order';
          details = ($('#orderNo').val()? ('Order '+$('#orderNo').val()) : 'Order');
          dateTxt = $('#orderDate').val()||'—';
        } else {
          forTxt = 'Charge';
          details = 'Settle ' + (($chargeRef.val()||'—'));
          dateTxt = $('#chargeDate').val()||'—';
        }
      }

      $('#phone').val(custPhone);
      $summaryCustomer.text(custName);
      $summaryType.text(typeTxt);
      $summaryFor.text(forTxt);
      $summaryDetails.text(details);
      $summaryDate.text(dateTxt);
      $summaryAmount.text(fmtKES(amountVal));
      syncApplyHint();
    }

    // Phone normalization (Kenya)
    $('#phone, #mpesaPhone').on('blur', function(){
      let v = $(this).val().replace(/\s|-/g,'');
      if (/^0[17]\d{8}$/.test(v)) v = '+254' + v.slice(1);
      if (/^7\d{8}$/.test(v)) v = '+254' + v;
      if (/^\+2547\d{8}$/.test(v)) $(this).val(v);
    });

    // Numeric guardrails
    $('[type="number"]').on('input', function(){
      const min = Number($(this).attr('min')||0);
      if (Number(this.value) < min) this.value = min;
    });

    // Events
    $('#entryTypeGroup input[name="entryTypeOpt"]').on('change', function(){ toggleEntryTypeUI(); updateSummary(); });
    $('#paymentForGroup input[name="paymentForOpt"]').on('change', function(){ togglePaymentForUI(); updateSummary(); });
    $('#customer, #orderNo, #orderNote, #debitReason, input[name="date"]').on('input change', updateSummary);
    $amount.on('input', updateSummary);

    // Init
    toggleEntryTypeUI();
    updateSummary();
    focusFirst();

    // Clear
    $('#clearBtn').on('click', function(){
      location.reload();
    });

    // Submit
    $form.on('submit', function(e){
      e.preventDefault(); 
      e.stopPropagation();
      $form.addClass('was-validated');
      if (!$form[0].checkValidity()) return;

      var payload = {
        entry_type: currentEntry(),                // receive | debit
        customer_id: $('#customer').val(),
        phone: $('#phone').val(),
        amount: $('#amount').val(),
        confirmed_at: "{{ now() }}",
      };

      if (payload.entry_type === 'receive') {
        payload.payment_method = $('input[name="paymentMethod"]:checked').val(); // cash | mpesa
        payload.payment_for = currentPaymentFor();
        const pf = currentPaymentFor();

        if (pf === 'subscription') {
          payload.date = $('#subscrDate').val();
          payload.subscription = { 
            plan: $('#plan').val(), 
            notes: $('#subscrNotes').val(), 
            subscription_id: $('#plan option:selected').data('subscription_id'), 
          };
        } else if (pf === 'order') {
          payload.date = $('#orderDate').val();
          payload.order = {
            order_id: $('#order').val(),
            order_no: $('#orderNo').val(),
            notes: $('#orderNotes').val() || null,
          };
        } else if (pf === 'charge') {
          payload.date = $('#chargeDate').val();
          payload.charge = {
            charge_id: $('#charge').val(),
            ref: $('#chargeRef').val(),
            notes: $('#chargeNotes').val(),
          };
        }

        payload.refs = {
          mpesa: $('#mpesaRef').val(),
          mpesa_phone: $('#mpesaPhone').val()
        };
      } else {
        // Lean debit: no line items, just reason/due/amount
        payload.date = $('#debitDue').val();
        payload.debit = { 
          notes: $('#debitNotes').val(), 
        };
      }

      // POST Data:
      $.ajax({
        url: "{{ route('biller.payment_receipts.store') }}",
        method:'POST',
        contentType:'application/json',
        data: JSON.stringify(payload),
        success: function(data) { 
          $('#saveToast').find('strong').text('Saved');
          $('#saveToast').find('.toast-body').removeClass('text-danger').text(data.message || '');
          quickToast(); 
        },
        error: function(xhr, status, error) { 
          const {message} = xhr?.responseJSON;
          if (message) {
            $('#saveToast').find('strong').text('Error');
            $('#saveToast').find('.toast-body').addClass('text-danger').text(message);
            quickToast();
          }
        }
      });
    });
  });
</script>
