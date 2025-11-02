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
                  <p class="mb-3 text-muted">Enter the customer's phone number and amount to initiate an M-Pesa STK Push.</p>

                  <div class="form-group mb-2">
                    <label for="mpesaPhonePrompt" class="font-weight-semibold">Phone Number (Safaricom)</label>
                    <input type="tel" class="form-control" id="mpesaPhonePrompt" name="mpesa_phone" placeholder="+2547XXXXXXXX" required>
                  </div>

                  <div class="form-group mb-2">
                    <label for="mpesaAmountPrompt" class="font-weight-semibold">Amount (KES)</label>
                    <input type="number" min="1" step="1" class="form-control" id="mpesaAmountPrompt" name="amount" placeholder="e.g. 500" required>
                  </div>

                  <div class="form-group mb-2">
                    <label for="mpesaOrderRef" class="font-weight-semibold">Reference / Order No.</label>
                    <input type="text" class="form-control" id="mpesaOrderRef" name="reference" placeholder="e.g. ORD-1001">
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