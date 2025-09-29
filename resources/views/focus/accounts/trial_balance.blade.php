@extends ('core.layouts.app')
@section ('title', 'Trial Balance | Accounting Reports')

@section('content')
<div class="content-wrapper pt-1">
    <div class="content-header row">
        <div class="content-header-left col-12">
            <h3 class="content-header-title font-weight-bold text-center">Trial Balance</h3>
        </div>
    </div>

    <!-- Filters -->
    <div class="content-body">
        <div class="row mb-1">
            <div class="col-8 col-xs-12 ml-auto mr-auto">
                <div class="row">
                    <div class="col-2">
                        <a href="{{ route('biller.accounts.trial_balance', 'csv') }}" class="btn btn-purple btn-sm" target="_blank" id="csv">
                            <i class="fa fa-print"></i> CSV Export
                        </a>
                    </div>                    
                </div>
                <div class="row">
                    <div class="col-9 pt-1">
                        <div class="row">
                            <div class="col-3" style="max-width: 150px;"><h5>Report Period</h5></div>
                            <div class="col-2 pl-0 pr-0">
                                <select id="report-period" class="form-control custom-select" style="height: 2em;">
                                    <option value="" start="{{ @$reportPeriods['today'][0] }}">Custom</option>
                                    @foreach (@$reportPeriods ?: [] as $key => $dateRange)
                                        <option value="{{ $key }}" start="{{ @$dateRange[0] }}" end="{{ @$dateRange[1] }}">
                                            {{ ucwords(implode(' ',preg_split('/(?=[A-Z])/', $key))) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-7">
                                <input type="hidden" id="start_date" class="d-inline col-4 mr-1 form-control datepicker start_date" style="height: 30px;" value="{{ @$reportPeriods['today'][0] }}">
                                <input type="text" id="end_date" class="d-inline col-4 mr-1 form-control datepicker end_date" style="height: 30px;" placeholder="{{ date('d-m-Y') }}">
                                <a href="{{ route('biller.accounts.trial_balance', 'v') }}" class="btn btn-info btn-sm search" id="search4">Search</a>
                                <a href="{{ route('biller.accounts.trial_balance', 'v') }}" class="btn btn-success btn-sm refresh" id="refresh">
                                    <i class="fa fa-refresh" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-8 col-xs-12 ml-auto mr-auto">
                <div class="card">
                    <div class="card-content print_me">
                        <div class="title bg-gradient-x-info white pb-3" role="button" data-toggle="collapse" data-target="#tbody">
                            <span class="float-right pt-1 pr-1" style="cursor:pointer;">&#9650;&#9660;</span>
                        </div>
                        <div class="responsive" style="max-height:80vh;overflow-y:auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="2"></th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody class="collapse multi-collapse show" id="tbody">
                                    @foreach ($accounts as $item)
                                        @if (in_array($item->account_type, ['Asset', 'Expense']))  
                                            <tr>
                                                <td>{{ $item->number }}</td>
                                                <td>{{ @$item->account_type_detail->name }}: <b>{{ $item->holder }}</b></td>
                                                <td>{{ numberFormat($item->balance) }}</td>
                                                <td></td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td>{{ $item->number }}</td>
                                                <td>{{ @$item->account_type_detail->name }}: <b>{{ $item->holder }}</b></td>
                                                <td></td>
                                                <td>{{ numberFormat($item->balance) }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    @php 
                                        $debitSum = $accounts->whereIn('account_type', ['Asset', 'Expense'])->sum('balance');
                                        $creditSum = $accounts->whereIn('account_type', ['Income', 'Liability', 'Equity'])->sum('balance');
                                        $diff = round($debitSum-$creditSum) 
                                    @endphp
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="font-weight-bold h5">{{ numberFormat($debitSum) }}</td>
                                        <td class="font-weight-bold h5">{{ numberFormat($creditSum) }} {!! $diff != 0 ? '<span class="text-danger"> ('. numberFormat($diff) .')</span>' : ''  !!}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
    
    // on change date input
    $(document).on('change', 'input', function() {
        let params = {
            report_period: $('#report-period').val(),
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val()
        };
        params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
        const url = "{{ route('biller.accounts.trial_balance', 'v') }}?" +(new URLSearchParams(params)).toString();
        $('#search4').attr('href', url);
    });
    
    $('#csv').click(function(){
        if (dates && dates.length) {
            $('#start_date').datepicker('setDate', new Date(dates[0]));
            $('#end_date').datepicker('setDate', new Date(dates[1]));
            let params = {
                report_period: $('#report-period').val(),
                start_date: $('#start_date').val(), 
                end_date: $('#end_date').val(),
            };
            params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
            const printUrl = "{{ route('biller.accounts.trial_balance', 'csv') }}?" + (new URLSearchParams(params)).toString();
            const searchUrl = "{{ route('biller.accounts.trial_balance', 'v') }}?" + (new URLSearchParams(params)).toString();
            $('#csv').attr('href', printUrl);
            $('#search4').attr('href', searchUrl);
        }
    });

    const dates = @json($dates);
    if (dates && dates.length) {
        $('#start_date').datepicker('setDate', new Date(dates[0]));
        $('#end_date').datepicker('setDate', new Date(dates[1]));
        let params = {
            report_period: $('#report-period').val(),
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val()
        };
        params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
        const printUrl = "{{ route('biller.accounts.trial_balance', 'p') }}?" + (new URLSearchParams(params)).toString();
        const searchUrl = "{{ route('biller.accounts.trial_balance', 'v') }}?" + (new URLSearchParams(params)).toString();
        const csvUrl = "{{ route('biller.accounts.trial_balance', 'csv') }}?" + (new URLSearchParams(params)).toString();
        $('#csv').attr('href', csvUrl);
        $('#print').attr('href', printUrl);
        $('#search4').attr('href', searchUrl);
    } 

    // Set Report Period
    $('#report-period').change(function() {
        const start = $(this).find(':selected').attr('start');
        const end = $(this).find(':selected').attr('end');
        if (start && end) {
            $('#start_date').val(start).trigger('change');
            $('#end_date').val(end).trigger('change');
            // $('#start_date, #end_date').attr('disabled', true);
        } else {
            $('#start_date').val('').trigger('change');
            $('#end_date').val('').trigger('change');
            // $('#start_date, #end_date').attr('disabled', false);
        }
    });
    const reportPeriod = @json(request('report_period'));
    if (reportPeriod) $('#report-period').val(reportPeriod).change();  
</script>
@endsection