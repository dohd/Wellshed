<div class="modal fade" id="mpesaModal" tabindex="-1" role="dialog" aria-labelledby="mpesaModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">MPESA Prompt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="mpesaPromptForm">
                <div class="modal-body">
                  <p class="mb-1 text-muted">Enter the customer's phone number to initiate an M-Pesa STK Push.</p>

                  <div class="form-group mb-2">
                    <label for="mpesaPhonePrompt" class="font-weight-semibold">Phone Number (Safaricom) <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="mpesaPhone" name="mpesa_phone" placeholder="2547XXXXXXXX" required>
                  </div>

                  <div class="form-group mb-2">
                    <label for="mpesaPaymentFor" class="font-weight-semibold">Payment For <span class="text-danger">*</span></label>
                    <input type="hidden" id="subscrId">
                    <input type="hidden" id="chargeId">
                    <select id="mpesaPaymentFor" class="form-control">
                      @if ($subscrPlan)
                      <option value="subscription" data-id="{{ $subscription->id }}" data-price="{{ +$subscrPlan->price }}">
                        {{ $subscrPlan->name }} Plan (KES {{ numberFormat($subscrPlan->price) }} / month)
                      </option>
                      @endif
                      @foreach ($charges as $charge)
                        <option value="charge" data-id="{{ $charge->id }}" data-amount="{{ $charge->amount }}">
                          {{ $charge->tid }}  - {{ $charge->notes }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group mb-2">
                    <label for="mpesaAmountPrompt" class="font-weight-semibold">Amount (KES) <span class="text-danger">*</span></label>
                    <input type="number" min="1" step="1" class="form-control" id="mpesaAmount" name="amount" placeholder="e.g. 500" readonly required>
                  </div>

                  <div class="form-group mb-2">
                    <label for="notes" class="font-weight-semibold">Notes (Optional)</label>
                    <input type="text" class="form-control" id="mpesaNotes" name="notes" placeholder="e.g. Pay for ORD-1001">
                  </div>

                  <div id="mpesaStatusArea" class="mt-3 d-none">
                    <div class="alert alert-info mb-0">
                      <i class="fas fa-spinner fa-spin mr-2"></i> Sending prompt to the customer’s phone...
                    </div>
                  </div>
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-success" id="btnSendMpesa">
                    <i class="fas fa-paper-plane mr-1"></i> Send Prompt
                  </button>
                </div>
            </form>
        </div>
    </div>
</div>