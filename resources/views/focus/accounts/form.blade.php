<div class="row">
    <div class="col-3 border-right border-secondary border-light" style="border-width:.5em!important;">
        <div class="border border-secondary border-light rounded p-2 mb-1 h5" style="background-color: #f5f7fa">
            An account tracks financial transactions for specific categories of 
            <b>Income, Expenses, Assets, Liabilities and Equity</b>. </br></br>
            Each account summarises transactions affecting a particular part of the company’s finances.
        </div>
        <div class="border border-secondary border-light rounded p-2 h5" style="background-color: #f5f7fa">
            <i class="fa fa-exclamation-circle" aria-hidden="true"></i> <b>Detail Type</b> 
            provides a more specific classification of the account, helping to further clarify its intended use. </br></br>
            Guides reporting by appropriately categorizing transactions for: <br>
            <b>Trial Balance </br>Income (P&L) Statement</br>Balance Sheet</br>Cash Flow Statement</b>.
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('account_type', trans('accounts.account_type')) }} <span class="text-danger">*</span>
            <select name="account_type" class="form-control custom-select" id="account-type" data-placeholder="Enter Account Type" required>
                <option value=""></option>
                @foreach ($account_types as $row)
                    <option 
                        value="{{ $row->category }}" 
                        key="{{ $row->id }}"
                        is-multiple="{{ $row->is_multiple }}"
                        is-opening-balance="{{ $row->is_opening_balance }}"
                        system="{{ $row->system }}"
                        {{ $row->id == @$account->account_type_id ? 'selected' : '' }}
                    >
                        {{ $row->name }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="account_type_id" id="account-type-id" value="{{ @$account->account_type_id }}">
            <input type="hidden" name="is_multiple" id="is-multiple">
        </div>
        <div class="form-group">
            {{ Form::label('account_type_detail', 'Detail Type') }}
            <select name="account_type_detail_id" class="form-control custom-select" id="detail-type" data-placeholder="Enter Detail Type">
                <option value=""></option>
                @if (@$account)
                    <option value="{{ $account->account_type_detail_id }}" selected>
                        {{ @$account->account_type_detail->name }}
                    </option>
                @endif
            </select>
        </div>
        <div class="form-group detail-type-descr border border-secondary border-light rounded p-2 h5" style="max-height:45vh; background-color: #f5f7fa">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('number', 'Number') }}
            {{ Form::text('number', null, ['class' => 'form-control', 'id' => 'account_number',]) }}
        </div>
        <div class="form-group">
            {{ Form::label('holder', 'Name') }} <span class="text-danger">*</span>
            {{ Form::text('holder', null, ['class' => 'form-control', 'placeholder' => 'Account Name', 'required' => 'required']) }}
        </div>
        <div class="form-group">        
            {{ Form::label('note', 'Description') }}
            {{ Form::text('note', null, ['class' => 'form-control', 'placeholder' => 'Description']) }}
        </div>
        <div class="form-group">
            {{ Form::label('currency', 'Currency') }}
            <select name="currency_id" class="form-control custom-select" id="currency">
                <option value="">-- Select --</option>
                @foreach ($currencies as $row) 
                    <option value="{{ $row->id }}" {{ $row->id == @$account->currency_id ? 'selected' : '' }}>
                        {{ $row->code }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="row mb-1">
            <div class="col-md-5">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="is_sub_account">
                    <label class="form-check-label" for="is-sub-account"> Is Sub-account</label>
                    {{ Form::hidden('is_sub_account', 0, ['id' => 'sub_account']) }}
                </div>
            </div>

            <div class="col-md-7">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="is_manual_journal">
                    <label class="form-check-label" for="is-manual-journal">Is Manual Journal Account</label>
                    {{ Form::hidden('is_manual_journal', null, ['id' => 'manual_journal']) }}
                </div>
            </div>
        </div>
        
        <div class="row mb-1">
            <div class="col-md-12">
                <select name="parent_id" class="form-control custom-select" id="parent_id" data-placeholder="Choose Parent account" disabled>
                    <option value=""></option>
                    @if (@$account)
                        <option value="{{ $account->parent_id }}" selected>{{ @$account->parent_account->holder }}</option>
                    @endif
                </select>
            </div>
        </div>
        <div class="form-group row no-gutters">
            <div class="col-md-5 mr-1">        
                {{ Form::label('opening_balance', 'Beginning Balance') }}           
                {{ Form::text('opening_balance', numberFormat(@$account->opening_balance), ['class' => 'form-control', 'id' => 'opening-balance']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('date', 'As of') }}
                {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
            </div>
        </div>
    </div>
</div>

@section('after-scripts')
@include('focus.accounts.form_js')
@endsection
