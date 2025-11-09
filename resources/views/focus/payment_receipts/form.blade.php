<style>
    body { background:#f7f9fb; }
    .card { border:0; border-radius:1rem; box-shadow:0 6px 20px rgba(0,0,0,.06); }
    .section-title { font-weight:600; font-size:.95rem; color:#6c757d; letter-spacing:.02em; text-transform:uppercase; margin-bottom:.5rem; }
    .required:after { content:" *"; color:#dc3545; }
    .pill-group .btn { border-radius:999px; }
    .pill-group .btn.active { box-shadow:0 0 0 .2rem rgba(0,123,255,.15); }
    .help { font-size:.85rem; color:#6c757d; }
    .sticky-side { position:sticky; top:1rem; }
    .divider { border-top:1px dashed #e6e9ef; margin:1rem 0; }
</style>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="mb-1 text-center">
        <h2 class="mb-1">Cash Entry</h2>
        <div class="help">Receive a payment (for Subscription, Order, or Charge) or create a new Charge (Debit).</div>
      </div>

      <div class="row">
        <!-- LEFT -->
        <div class="col-lg-7">
          <div class="card">
            <div class="card-body">
              <!-- <form id="entryForm" novalidate> -->
                <!-- ENTRY TYPE -->
                <div class="mb-1">
                  <div class="section-title">Entry Type</div>
                  <div class="btn-group btn-group-toggle pill-group d-flex flex-wrap" data-toggle="buttons" id="entryTypeGroup">
                    <label class="btn btn-outline-primary mr-2 mb-2 active">
                      <input type="radio" name="entryTypeOpt" value="receive" checked> Receive Payment
                    </label>
                    <label class="btn btn-outline-primary mr-2 mb-2">
                      <input type="radio" name="entryTypeOpt" value="debit"> Charge Customer (Debit)
                    </label>
                  </div>
                  <div id="entryHint" class="help mt-1">You’re recording money received (Dr Cash/Bank, Cr A/R).</div>
                </div>

                <!-- PAYMENT FOR (Receive mode only) -->
                <div id="paymentForWrap" class="mb-1">
                  <div class="section-title">Payment For</div>
                  <div class="btn-group btn-group-toggle pill-group d-flex flex-wrap" data-toggle="buttons" id="paymentForGroup">
                    <label class="btn btn-outline-secondary mr-2 mb-2 active">
                      <input type="radio" name="paymentForOpt" value="subscription" checked> Subscription
                    </label>
                    <label class="btn btn-outline-secondary mr-2 mb-2">
                      <input type="radio" name="paymentForOpt" value="order"> Order
                    </label>
                    <label class="btn btn-outline-secondary mr-2 mb-2">
                      <input type="radio" name="paymentForOpt" value="charge"> Charge (existing)
                    </label>
                  </div>
                  <div id="contextHint" class="help mt-1">This receipt will be linked to a subscription.</div>
                </div>

                <div class="divider"></div>

                <!-- CUSTOMER -->
                <div class="mb-1" id="customerBlock">
                  <div class="section-title">Customer</div>
                  <div class="form-row">
                    <div class="form-group col-12 col-md-8">
                      <label class="required" for="customer">Select Customer</label>
                      <select class="form-control" name="customer_id" id="customer" data-placeholder="Choose customer" >
                        <option value=""></option>
                        @foreach ($customers as $customer)
                          <option value="{{ $customer->id }}" 
                            data-name="{{ $customer->company ?: $customer->name }}"
                            data-phone="{{ $customer->phone }}"
                          >
                           {{ $customer->company ?: $customer->name }}
                          </option>
                        @endforeach
                      </select>
                      <div class="invalid-feedback">Please select a customer.</div>
                    </div>
                    <div class="form-group col-12 col-md-4">
                      <label class="required" for="phone">Phone</label>
                      <input type="tel" inputmode="tel" autocomplete="tel" class="form-control" id="phone" placeholder="+2547XXXXXXXX" readonly>
                      <div class="invalid-feedback">Enter a valid phone number.</div>
                    </div>
                  </div>
                </div>

                <!-- SUBSCRIPTION (Receive) -->
                <div id="subscriptionSection" class="mb-1">
                  <div class="section-title">Subscription Details</div>
                  <div class="form-row">
                    <div class="form-group col-12 col-md-6">
                      <label class="required" for="plan">Plan</label>
                      <select id="plan" class="form-control" >
                        <option value="">— Choose Plan —</option>
                        <!-- <option value="starter" data-amount="3000" data-name="Starter Monthly">Starter — Monthly</option> -->
                      </select>
                      <div class="invalid-feedback">Please select a plan.</div>
                      <div class="help mt-1">Choosing a plan auto-fills the amount (you can override).</div>
                    </div>
                    <div class="form-group col-6 col-md-6">
                      <label class="required" for="subscrDate">Payment Date</label>
                      <input name="date" id="subscrDate" type="date" class="form-control" >
                      <div class="invalid-feedback">Choose the payment date.</div>
                    </div>
                    <div class="form-group col-12 col-md-12">
                      <label for="orderNote">Note (optional)</label>
                      <textarea name="notes" rows="2" id="subscrNotes" class="form-control" placeholder="e.g. Part payment"></textarea>
                    </div>
                  </div>
                </div>

                <!-- ORDER (Receive) — LEAN -->
                <div id="orderSection" class="mb-1" style="display:none;">
                  <div class="section-title">Order Details</div>
                  <div class="help mb-2">Just reference the order you’re receiving for. Enter the amount below.</div>
                  <div class="form-row">
                    <div class="form-group col-12 col-md-6">
                      <label class="required" for="orderNo">Order No.</label>
                      <select id="order" class="form-control" >
                        <option value="">— Choose Order —</option>                        
                      </select>
                      <input id="orderNo" type="hidden" class="form-control" placeholder="e.g. ORD-12345" >
                      <div class="invalid-feedback">Enter order number.</div>
                    </div>
                    <div class="form-group col-6 col-md-6">
                      <label class="required" for="orderDate">Payment Date</label>
                      <input name="date" id="orderDate" type="date" class="form-control" >
                      <div class="invalid-feedback">Choose the payment date.</div>
                    </div>
                    <div class="form-group col-12 col-md-12">
                      <label for="orderNote">Note (optional)</label>
                      <textarea name="notes" rows="2" id="orderNotes" class="form-control" placeholder="e.g. Part payment"></textarea>
                    </div>
                  </div>
                </div>

                <!-- CHARGE (Receive against existing charge) -->
                <div id="chargeReceiveSection" class="mb-1" style="display:none;">
                  <div class="section-title">Charge to Settle</div>
                  <div class="help mb-2">Enter the charge/debit reference you are receiving payment for.</div>
                  <div class="form-row">
                    <div class="form-group col-12 col-md-6">
                      <label class="required" for="chargeRef">Charge Ref (ID / No.)</label>
                      <select id="charge" class="form-control" >
                        <option value="">— Choose Charge —</option>                        
                      </select>
                      <input id="chargeRef" type="hidden" class="form-control" placeholder="e.g. CHG-1058" >
                      <div class="invalid-feedback">Enter the charge reference.</div>
                    </div>
                    <div class="form-group col-6 col-md-6">
                      <label class="required" for="chargeDate">Payment Date</label>
                      <input name="date" id="chargeDate" type="date" class="form-control" >
                      <div class="invalid-feedback">Choose the payment date.</div>
                    </div>
                    <div class="form-group col-12 col-md-12">
                      <label for="orderNote">Note (optional)</label>
                      <textarea name="notes" rows="2" id="chargeNotes" class="form-control" placeholder="e.g. Part payment"></textarea>
                    </div>                    
                  </div>
                </div>

                <!-- DEBIT (lean: no line items) -->
                <div id="debitSection" class="mb-1 d-none">
                  <div class="section-title">Debit Details</div>
                  <div class="help mb-2">Creates a receivable (no payment method here). Set a due date and amount.</div>
                  <div class="form-row">
                    <div class="form-group col-6 col-md-5">
                      <label class="required" for="debitDue">Due Date</label>
                      <input id="debitDue" type="date" class="form-control">
                      <div class="invalid-feedback">Select a due date.</div>
                    </div>
                    <div class="form-group col-12 col-md-12">
                      <label class="required" for="debitReason">Reason / Charge Name</label>
                      <textarea rows="2" name="notes" id="debitNotes" type="text" class="form-control" placeholder="e.g. Lost 18.9L Bottle"></textarea>
                      <div class="invalid-feedback">Provide a reason.</div>
                    </div>                    
                  </div>
                </div>

                <div class="divider"></div>

                <!-- AMOUNT (used by both Receive and Debit) -->
                <div class="mb-1">
                  <!-- <div class="section-title">Amount</div> -->
                  <div class="form-row">
                    <div class="form-group col-12 col-md-8">
                      <label class="required" for="amount">Amount</label>
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">KES</span></div>
                        <input id="amount" type="number" inputmode="numeric" step="1" min="1" autocomplete="off" class="form-control" >
                      </div>
                      <div class="invalid-feedback">Amount is required.</div>
                      <div id="applyHint" class="help mt-1 d-none">Applying <span id="applyNow">KES 0</span> to charge <span id="applyRefShow">—</span>.</div>
                    </div>
                  </div>
                </div>

                <!-- PAYMENT METHOD (Receive only) -->
                <div class="mb-1" id="paymentMethodBlock">
                  <div class="section-title">Payment Method</div>
                  <div class="row">
                    {{-- <div class="col-12 col-md-4 mb-2">
                      <div class="custom-control custom-radio">
                        <input type="radio" id="pmCash" name="paymentMethod" class="custom-control-input" value="cash">
                        <label class="custom-control-label" for="pmCash">Cash</label>
                      </div>
                    </div> --}}
                    <div class="col-12 col-md-4 mb-2">
                      <div class="custom-control custom-radio">
                        <input type="radio" id="pmMpesa" name="paymentMethod" class="custom-control-input" value="mpesa" checked>
                        <label class="custom-control-label" for="pmMpesa">M-Pesa</label>
                      </div>
                    </div>
                  </div>

                  <!-- M-Pesa fields -->
                  <div id="mpesaFields" class="mt-3">
                    <div class="form-row">
                      <div class="form-group col-12 col-md-6">
                        <label class="required" for="mpesaRef">M-Pesa Code</label>
                        <input type="text" class="form-control" id="mpesaRef" placeholder="e.g. QJK3XYZ1" >
                        <div class="invalid-feedback">M-Pesa reference is required.</div>
                      </div>
                      <div class="form-group col-12 col-md-6">
                        <label for="mpesaPhone">Paid From (optional)</label>
                        <input type="tel" inputmode="tel" class="form-control" id="mpesaPhone" placeholder="+2547XXXXXXXX">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ACTIONS -->
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center">
                  <button type="submit" id="primaryCta" class="btn btn-success btn-lg d-block d-md-inline-block w-100 w-md-auto">Record Payment</button>
                  <button type="reset" class="btn btn-outline-secondary d-block d-md-inline-block w-100 w-md-auto ml-md-2 mt-2 mt-md-0" id="clearBtn">Clear</button>
                  <!-- <small class="text-muted mt-2 mt-md-0 ml-md-auto text-center text-md-left">KES currency assumed.</small> -->
                </div>
              <!-- </form> -->
            </div>
          </div>
        </div>

        <!-- RIGHT: SUMMARY -->
        <div class="col-lg-5">
          <div class="card sticky-side">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Entry Summary</h5>
                <span class="badge badge-pill badge-info" id="summaryMethod">M-Pesa</span>
              </div>
              <hr class="mt-2">
              <dl class="row mb-0">
                <dt class="col-5 text-muted">Customer</dt>
                <dd class="col-7" id="summaryCustomer">—</dd>

                <dt class="col-5 text-muted">Type</dt>
                <dd class="col-7" id="summaryType">Receive Payment</dd>

                <dt class="col-5 text-muted">For</dt>
                <dd class="col-7" id="summaryFor">Subscription</dd>

                <dt class="col-5 text-muted">Details</dt>
                <dd class="col-7" id="summaryDetails">—</dd>

                <dt class="col-5 text-muted">Date</dt>
                <dd class="col-7" id="summaryDate">—</dd>

                <dt class="col-5 text-muted">Amount</dt>
                <dd class="col-7 font-weight-bold" id="summaryAmount">KES 0</dd>
              </dl>
              <hr>
              <p class="small text-muted mb-0">Debits create receivables; record a receipt later to settle.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Toast -->
      <div aria-live="polite" aria-atomic="true" style="position:fixed; top:1rem; right:1rem; z-index:1080">
        <div class="toast" id="saveToast" data-delay="3500">
          <div class="toast-header">
            <strong class="mr-auto">Saved</strong><small>now</small>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
          </div>
          <div class="toast-body">Entry recorded successfully.</div>
        </div>
      </div>

    </div> <!-- /.col-lg-10 -->
  </div>
</div>

@section('after-scripts')
@include('focus.payment_receipts.form_js')
@endsection
