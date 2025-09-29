{{ Form::open(['route' => array('biller.reports.generate_tax_statement','tax'), 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post','files' => false, 'id' => 'statement']) }}

<div class="form-group row">
    <label class="col-sm-3 col-form-label"
           for="pay_cat">{{trans('transactions.transaction_type')}}</label>

    <div class="col-sm-3">
        <select name="tax_type" class="form-control">
            <option value='tax_sales'>{{trans('meta.tax_statement')}}</option>
            <option value='tax_purchase'>{{trans('meta.tax_statement_purchase')}}</option>
            <option value='tax_sale_purchase'>Combined Tax Statement</option>

        </select>


    </div>
</div>
<div class="form-group row">
    <label for="record_month" class="col-sm-3">Sale / Purchase Month From</label>
    <div class="col-sm-3">
        {{ Form::text('record_month', request('record_month'), ['class' => 'form-control record_month', 'id' => 'record_month', 'required']) }}
    </div>
</div>
<div class="form-group row">
    <label for="month_to" class="col-sm-3">Sale / Purchase Month To</label>
    <div class="col-sm-3">
        {{ Form::text('month_to', request('month_to'), ['class' => 'form-control record_month', 'id' => 'month_to', 'required']) }}
    </div>
</div>
<div class="form-group row d-none">

    <label class="col-sm-3 control-label"
           for="sdate">{{trans('meta.from_date')}}</label>

    <div class="col-sm-3">
        <input type="text" class="form-control from_date required"
               placeholder="Start Date" name="from_date"
               autocomplete="false" data-toggle="datepicker">
    </div>
</div>
<div class="form-group row d-none">

    <label class="col-sm-3 control-label"
           for="edate">{{trans('meta.to_date')}}</label>

    <div class="col-sm-3">
        <input type="text" class="form-control required to_date"
               placeholder="End Date" name="to_date"
               data-toggle="datepicker" autocomplete="false">
    </div>
</div>
<div class="form-group row">
    <label for="tax_group" class="col-sm-3">Tax Group</label>
    <div class="col-sm-3">
        @php
            $options = [
                '16' => 'General Rated Sales/Purchases (16%)',
                '8' => 'Other Rated Sales/Purchases (8%)',
                '0' => 'Zero Rated Sales/Purchases (0%)',
                '00' => 'Exempted Rated Sales/Purchases',
            ]
        @endphp
        <select name="tax_group" id="tax_group" class="custom-select">
            {{-- <option value="">-- select tax group --</option> --}}
            @foreach ($options as $key => $val)
                <option value="{{ intval($key) }}" {{ in_array(request('tax_group'), ['16', '8', '0']) && intval($key) == request('tax_group')? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label"
           for="pay_cat">{{trans('meta.report_output_format')}}</label>

    <div class="col-sm-3">
        <select name="output_format" class="form-control">
            <option value='pdf_print'>{{trans('general.pdf_print')}}</option>
            <option value='pdf'>{{trans('general.pdf')}}</option>
            <option value='csv'>CSV</option>
        </select>


    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="pay_cat"></label>

    <div class="col-sm-4">
        <input type="submit" class="btn btn-primary btn-md" value="View">


    </div>
</div>

{{ Form::close() }}
