{{-- General --}}
<div class="mb-3">
    <label>Environment</label>
    <select name="env" class="form-control">
        <option value="sandbox" {{ @$mpesaConfig->env == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
        <option value="production" {{ @$mpesaConfig->env == 'production' ? 'selected' : '' }}>Production</option>
    </select>
</div>

<div class="mb-3">
    <label>Type</label>
    <select id="mpesa-type" name="type" class="form-control">
        <option value="b2c" {{ @$mpesaConfig->type == 'b2c' ? 'selected' : '' }}>B2C</option>
        <option value="c2b_store" {{ @$mpesaConfig->type == 'c2b_store' ? 'selected' : '' }}>C2B Store</option>
        <option value="c2b_paybill" {{ @$mpesaConfig->type == 'c2b_paybill' ? 'selected' : '' }}>C2B PayBill</option>
        <option value="stk_push" {{ @$mpesaConfig->type == 'stk_push' ? 'selected' : '' }}>STK Push</option>
    </select>
</div>

<div id="field-shortcode" class="mb-3">
    <label>Shortcode</label>
    <input type="text" name="shortcode" class="form-control" value="{{ @$mpesaConfig->shortcode }}">
</div>

<div id="field-head-office-shortcode" class="mb-3">
    <label>Head Office Shortcode</label>
    <input type="text" name="head_office_shortcode" class="form-control"
        value="{{ @$mpesaConfig->head_office_shortcode }}">
</div>

{{-- B2C --}}
<div id="field-initiator-name" class="mb-3">
    <label>Initiator Name</label>
    <input type="text" name="initiator_name" class="form-control" value="{{ @$mpesaConfig->initiator_name }}">
</div>

<div id="field-initiator-password" class="mb-3">
    <label>Initiator Password (Encrypted)</label>
    <input type="text" name="initiator_password_enc" class="form-control"
        value="{{ @$mpesaConfig->initiator_password_enc }}">
</div>

<div class="mb-3">
    <label>Consumer Key</label>
    <input type="text" name="consumer_key" class="form-control"
        value="{{ @$mpesaConfig->consumer_key }}">
</div>
<div class="mb-3">
    <label>Consumer Secret</label>
    <input type="text" name="consumer_secret" class="form-control"
        value="{{ @$mpesaConfig->consumer_secret }}">
</div>

{{-- <div class="mb-3">
    <label>Command ID</label>
    <input type="text" name="command_id" class="form-control" value="{{ @$mpesaConfig->command_id }}">
</div> --}}

<div id="field-result-url" class="mb-3">
    <label>Result URL</label>
    <input type="text" name="result_url" class="form-control" value="{{ @$mpesaConfig->result_url }}">
</div>

<div id="field-timeout-url" class="mb-3">
    <label>Timeout URL</label>
    <input type="text" name="timeout_url" class="form-control" value="{{ @$mpesaConfig->timeout_url }}">
</div>

{{-- C2B --}}
<div id="field-validation-url" class="mb-3">
    <label>Validation URL</label>
    <input type="text" name="validation_url" class="form-control" value="{{ @$mpesaConfig->validation_url }}">
</div>

<div id="field-confirmation-url" class="mb-3">
    <label>Confirmation URL</label>
    <input type="text" name="confirmation_url" class="form-control" value="{{ @$mpesaConfig->confirmation_url }}">
</div>

{{-- STK Push --}}
<div id="field-passkey" class="mb-3">
    <label>Passkey</label>
    <input type="text" name="passkey" class="form-control" value="{{ @$mpesaConfig->passkey }}">
</div>

<div id="field-account-reference" class="mb-3">
    <label>Account Reference</label>
    <input type="text" name="account_reference" class="form-control" value="{{ @$mpesaConfig->account_reference }}">
</div>

<div id="field-callback-url" class="mb-3">
    <label>Callback URL</label>
    <input type="text" name="callback_url" class="form-control" value="{{ @$mpesaConfig->callback_url }}">
</div>

{{-- Certificates --}}
<div id="field-cert_file" class="mb-3">
    <label>Certificate File</label>
    <input type="file" name="cert_file" class="form-control">
    @if(!empty(@$mpesaConfig->cert_path))
        <small class="text-muted">Current: {{ @$mpesaConfig->cert_path }}</small>
    @endif
</div>
