@extends ('core.layouts.app')
@section ('title', 'Balance Sheet | Accounting Reports')

@section('content')
<style>
    .card {
        margin-bottom: 1rem !important;
    }
    .card-content {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .card-footer td {
        border-top: none;
        padding-left: 5px !important;
        padding-right: 5px !important;
    }
    .card-footer td:nth-child(2) {
        padding-left: 2 rem!important;
    }
    td {
        width: 50% !important;
    }
</style>
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-12">
            <h3 class="content-header-title font-weight-bold text-center">Balance Sheet</h3>
        </div>
    </div>

    <div class="content-body">
        <div class="row mb-1">
            <div class="col-8 col-xs-12 ml-auto mr-auto">
                <div class="row">
                    <div class="col-2 pt-1">
                        <a href="{{ route('biller.accounts.balance_sheet', 'csv') }}" class="btn btn-purple btn-sm" target="_blank" id="csv">
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
                                    @foreach ($reportPeriods as $key => $dateRange)
                                        <option value="{{ $key }}" start="{{ @$dateRange[0] }}" end="{{ @$dateRange[1] }}">
                                            {{ ucwords(implode(' ',preg_split('/(?=[A-Z])/', $key))) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-7">
                                <input type="hidden" id="start_date" class="d-inline col-4 mr-1 form-control datepicker start_date" style="height: 30px;" value="{{ @$reportPeriods['today'][0] }}">
                                <input type="text" id="end_date" class="d-inline col-4 mr-1 form-control datepicker end_date" style="height: 30px;" placeholder="{{ date('d-m-Y') }}">
                                <a href="{{ route('biller.accounts.balance_sheet', 'v') }}" class="btn btn-info btn-sm search" id="search4">Search</a>
                                <a href="{{ route('biller.accounts.balance_sheet', 'v') }}" class="btn btn-success btn-sm refresh" id="refresh">
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
                <!-- Assets Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-info white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#assetTab">
                        <span>Assets</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show" id="assetTab">
                        <div class="card-content">
                            <div class="responsive" style="max-height:80vh;overflow-y:auto">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr><td colspan="2">&#9660;Current Assets</td></tr>
                                        <!-- Bank Section -->
                                        <tr><td colspan="2"><span class="ml-1" style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#bankRow">&#9660;Bank Accounts</span></td></tr>
                                        @foreach ($accountTypeDetails->whereIn('system_rel', ['cash', 'bank']) as $detail)
                                            <tr class="collapse multi-collapse show" id="bankRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach 
                                        @endforeach
                                        <tr>
                                            @php $bankSum = $accountTypeDetails->whereIn('system_rel', ['cash', 'bank'])->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Bank Accounts</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($bankSum) }}</td>
                                        </tr>
                                        <!-- Accounts Receivable Section -->
                                        <tr><td colspan="2"><span class="ml-1" style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#receivableRow">&#9660;Accounts Receivable</span></td></tr>
                                        @foreach ($accountTypeDetails->whereIn('system_rel', ['receivable', 'loan']) as $detail)
                                            <tr class="collapse multi-collapse show" id="receivableRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $receivableSum = $accountTypeDetails->whereIn('system_rel', ['receivable', 'loan'])->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Accounts Receivable</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($receivableSum) }}</td>
                                        </tr>
                                        <!-- Other Current Assets Section-->
                                        <tr><td colspan="2"><span class="ml-1" style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#otherCurrentAssetRow">&#9660;Other Current Assets</span></td></tr>
                                        @foreach ($accountTypeDetails->where('system_rel', 'current_asset') as $detail)
                                            <tr class="collapse multi-collapse show" id="otherCurrentAssetRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                @if (@$account->account_type_detail->system == 'work_in_progress')
                                                    <tr>
                                                        <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                        <td>{{ numberFormat($account->balance) }}</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                        <td>{{ numberFormat($account->balance) }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $otherCurrentAssetSum = $accountTypeDetails->where('system_rel', 'current_asset')->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Other Current Assets</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($otherCurrentAssetSum) }}</td>
                                        </tr>
                                        <!-- Fixed Assets Section -->
                                        <tr><td colspan="2"><span style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#fixedAssetRow">&#9660;Fixed Assets</span></td></tr>
                                        @foreach ($accountTypeDetails->where('system_rel', 'fixed_asset') as $detail)
                                            <tr class="collapse multi-collapse show" id="fixedAssetRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $fixedAssetSum = $accountTypeDetails->where('system_rel', 'fixed_asset')->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Fixed Assets</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($fixedAssetSum) }}</td>
                                        </tr>
                                        <!-- Other Assets Section -->
                                        <tr><td colspan="2"><span style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#otherAssetRow">&#9660;Other Assets</span></td></tr>
                                        @foreach ($accountTypeDetails->where('system_rel', 'other_asset') as $detail)
                                            <tr class="collapse multi-collapse show" id="otherAssetRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $otherAssetSum = $accountTypeDetails->where('system_rel', 'other_asset')->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Other Assets</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($otherAssetSum) }}</td>
                                        </tr>
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $assetSum = $accountTypeDetails->where('category', 'Asset')->sum('balance') @endphp
                                    <td><b>Total Assets</b></td>
                                    <td class="font-weight-bold">{{ numberFormat($assetSum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Liabilities Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-purple white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#liabilitiesTab">
                        <span>Liabilities</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show " id="liabilitiesTab">
                        <div class="card-content">
                            <div class="responsive" style="max-height:80vh;overflow-y:auto">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr><td colspan="2"><span>&#9660;Current Liabilities</span></td></tr>
                                        <!-- Accounts Payable Section -->
                                        <tr><td colspan="2"><span class="ml-1" style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#payableRow">&#9660;Accounts Payable</span></td></tr>
                                        @foreach ($accountTypeDetails->whereIn('system_rel', ['payable']) as $detail)
                                            <tr class="collapse multi-collapse show" id="payableRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $payableSum = $accountTypeDetails->whereIn('system_rel', ['payable'])->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Accounts Payable</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($payableSum) }}</td>
                                        </tr>
                                        <!-- Payroll Liabilities Section -->
                                        <tr><td colspan="2"><span class="ml-1" style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#payrollRow">&#9660;Payroll Liabilities</span></td></tr>
                                        @foreach ($accountTypeDetails->where('system_rel', 'payroll_liability') as $detail)
                                            <tr class="collapse multi-collapse show" id="payrollRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $payrollSum = $accountTypeDetails->where('system_rel', 'payroll_liability')->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Payroll Liabilities</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($payrollSum) }}</td>
                                        </tr>
                                        <!-- Credit Cards Section -->
                                        <tr><td colspan="2"><span class="ml-1" style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#salesTaxRow">&#9660;Credit Cards</span></td></tr>
                                        @foreach ($accountTypeDetails->where('system', 'credit_card') as $detail)
                                            <tr class="collapse multi-collapse show" id="salesTaxRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $creditCardSum = $accountTypeDetails->where('system', 'credit_card')->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Credit Cards</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($creditCardSum) }}</td>
                                        </tr>
                                        <!-- Other Current Liabilities Section -->
                                        <tr><td colspan="2"><span class="ml-1" style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#othercurrentLiabilityRow">&#9660;Other Current Liabilities</span></td></tr>
                                        @foreach ($accountTypeDetails->where('system_rel', 'other_current_liability') as $detail)
                                            <tr class="collapse multi-collapse show" id="othercurrentLiabilityRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $otherCurrentLiabilitySum = $accountTypeDetails->where('system_rel', 'other_current_liability')->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Other Current Liabilities</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($otherCurrentLiabilitySum) }}</td>
                                        </tr>
                                        <!-- Long-Term Liabilities Section -->
                                        <tr><td colspan="2"><span style="cursor:pointer;" role="button" data-toggle="collapse" data-target="#longTermLiabilityRow">&#9660;Long-Term Liabilities</span></td></tr>
                                        @foreach ($accountTypeDetails->whereIn('system_rel', ['loan', 'long_term_liability']) as $detail)
                                            <tr class="collapse multi-collapse show" id="longTermLiabilityRow">
                                                <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <tr>
                                            @php $longtermLiabilitySum = $accountTypeDetails->whereIn('system_rel', ['loan', 'long_term_liability'])->sum('balance') @endphp
                                            <td><span class="ml-2 font-weight-bold">Total Long-Term Liabilities</span></td>
                                            <td class="font-weight-bold">{{ numberFormat($longtermLiabilitySum) }}</td>
                                        </tr>
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $liabilitySum = $accountTypeDetails->where('category', 'Liability')->sum('balance') @endphp
                                    <td class="font-weight-bold">Total Liabilities</td>
                                    <td class="font-weight-bold">{{ numberFormat($liabilitySum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Equity Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-blue white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#equityTab">
                        <span>Equity</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show " id="equityTab">
                        <div class="card-content">
                            <div class="responsive" style="max-height:80vh;overflow-y:auto">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach (['equity', 'owner_equity', 'retained_earning'] as $relation)
                                            @foreach ($accountTypeDetails->where('system_rel', $relation) as $detail)
                                                <tr class="collapse multi-collapse show" id="equityRow">
                                                    <td><span class="ml-2">{{ $detail->name }}</span></td>
                                                    <td></td>
                                                </tr>
                                                @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                    <tr>
                                                        <td><span class="ml-3"><a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a></span></td>
                                                        <td>{{ numberFormat($account->balance) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                        <tr class="collapse multi-collapse show" id="equityRow">
                                            <td><span class="ml-2">Net Profit</span></td>
                                            <td>{{ numberFormat($netProfit) }}</td>
                                        </tr>
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php 
                                        $equitySum = $accountTypeDetails->whereIn('system_rel', ['equity', 'owner_equity', 'retained_earning'])->sum('balance');
                                        $equitySum += $netProfit;
                                    @endphp
                                    <td><span class="ml-2 font-weight-bold">Total Equity</span></td>
                                    <td class="font-weight-bold">{{ numberFormat($equitySum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Balance Sheet Equation -->
                <div class="card">
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php 
                                        $assetDiff = 0;
                                        $equityDiff = 0;
                                        $liabilityDiff = 0;
                                        if ($assetSum != $liabilitySum + $equitySum) {
                                            $assetDiff = round($liabilitySum + $equitySum - $assetSum, 2);
                                        } 
                                    @endphp                                    
                                    <td class="font-weight-bold">Total Assets = Total Liabilities + Total Equity</td>
                                    <td class="font-weight-bold">
                                        {{ numberFormat($assetSum) }} {!! $assetDiff != 0 ? '<span class="text-danger"> ('. numberFormat($assetDiff) .')</span>' : ''  !!} = 
                                        {{ numberFormat($liabilitySum) }}  + 
                                        {{ numberFormat($equitySum) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>  
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
        const url = "{{ route('biller.accounts.balance_sheet', 'v') }}?" +(new URLSearchParams(params)).toString();
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
            const printUrl = "{{ route('biller.accounts.balance_sheet', 'csv') }}?" + (new URLSearchParams(params)).toString();
            const searchUrl = "{{ route('biller.accounts.balance_sheet', 'v') }}?" + (new URLSearchParams(params)).toString();
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
        const printUrl = "{{ route('biller.accounts.balance_sheet', 'p') }}?" + (new URLSearchParams(params)).toString();
        const searchUrl = "{{ route('biller.accounts.balance_sheet', 'v') }}?" + (new URLSearchParams(params)).toString();
        const csvUrl = "{{ route('biller.accounts.balance_sheet', 'csv') }}?" + (new URLSearchParams(params)).toString();
        $('#csv').attr('href', csvUrl);
        $('#print').attr('href', printUrl);
        $('#search4').attr('href', searchUrl);
    } 

    // Set Report Period
    $('#report-period').change(function() {
        const start = $(this).find(':selected').attr('start');
        if (start) $('#start_date').val(start).trigger('change');
        else $('#start_date').val('').trigger('change');
        
        const end = $(this).find(':selected').attr('end');
        if (end) $('#end_date').val(end).trigger('change');
        else $('#end_date').val('').trigger('change');
    });
    const reportPeriod = @json(request('report_period'));
    if (reportPeriod) $('#report-period').val(reportPeriod).change();    
</script>
@endsection
