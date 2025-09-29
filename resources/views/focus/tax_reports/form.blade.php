<div class="card">
    <div class="card-content">
        <div class="card-body">
            <div class="row form-group">
                <div class="col-3">
                    <label for="record_month">Sale / Purchase Month</label>
                    {{ Form::text('record_month', @$prev_month, ['class' => 'form-control datepicker', 'id' => 'record_month', 'required']) }}
                </div>

                <div class="col-3">
                    <label for="tax_group">Tax Group</label>
                    @php
                        $taxRange = array_map(fn($v) => +$v, $taxRange);
                        // dd($taxRange);
                        $options = [
                            '16' => in_array(16, $taxRange)? 'General Rated Sales/Purchases (16%)' : '',
                            // '8' => 'Other Rated Sales/Purchases (8%)',
                            '0' => in_array(0, $taxRange)? 'Zero Rated Sales/Purchases (0%)' : '',
                            '00' => 'Exempted Rated Sales/Purchases',
                        ]
                    @endphp
                    <select name="tax_group" id="tax_group" class="custom-select">
                        <option value="">-- select tax group --</option>
                        @foreach ($options as $key => $val)
                            <option value="{{ intval($key) }}" key="{{ $key }}" {{ @$tax_report && intval($key) == $tax_report->tax_group? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-3">
                    <label for="return_month">Return Month</label>
                    {{ Form::text('return_month', @$prev_month, ['class' => 'form-control datepicker', 'id' => 'return_month', 'required']) }}
                </div>
            </div>

            <div class="row form-group">
                <div class="col-9">
                    <label for="note">Note</label>
                    {{ Form::text('note', null, ['class' => 'form-control', 'required' => 'required']) }}
                </div>
            </div>
            
            {{-- tab menu --}}
            <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Sales</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Purchases</a>
                </li>                                     
            </ul>
            <div class="tab-content px-1 pt-1">
                {{-- sales tab --}}
                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                    @include('focus.tax_reports.tabs.sales')
                </div>
                {{-- purchases tab --}}
                <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                    @include('focus.tax_reports.tabs.purchases')
                </div>
                <div class="form-group row no-gutters">
                    <div class="col-1">
                        <a href="{{ route('biller.tax_reports.index') }}" class="btn btn-danger block">Cancel</a>    
                    </div>&nbsp;
                    <div class="col-1">
                        {{ Form::submit(@$tax_report? 'Update' : 'Generate', ['class' => 'form-control btn btn-primary text-white']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('extra-scripts')
@include('focus.tax_reports.form_js')
@endsection
