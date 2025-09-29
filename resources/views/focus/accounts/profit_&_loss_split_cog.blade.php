@extends ('core.layouts.app')
@section ('title', 'Profit And Loss | Accounting Reports')

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
        padding-left: 2rem !important;
    }
    td {
        width: 50% !important;
    }
</style>
<div class="content-wrapper pt-1">
    <div class="content-header row mb-1">
        <div class="content-header-left col-12">
            <h3 class="content-header-title font-weight-bold text-center">Profit And Loss</h3>
        </div>
    </div>

    <div class="content-body">
        <!-- Filters -->
        <div class="row mb-1">
            <div class="col-8 col-xs-12 ml-auto mr-auto">
                <div class="row">
                    <div class="col-2 pt-1">
                        <a href="{{ route('biller.accounts.profit_and_loss', 'csv') }}" class="btn btn-purple btn-sm" target="_blank" id="csv">
                            <i class="fa fa-print"></i> CSV Export
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-9 pt-1">
                        <div class="row">
                            <div class="col-2"><h5>Report Period</h5></div>
                            <div class="col-2 pl-0 pr-0">
                                <select id="report-period" class="form-control custom-select" style="height: 2em;">
                                    <option value="">Custom</option>
                                    @foreach ($reportPeriods as $key => $dateRange)
                                        <option value="{{ $key }}" start="{{ @$dateRange[0] }}" end="{{ @$dateRange[1] }}">
                                            {{ ucwords(implode(' ',preg_split('/(?=[A-Z])/', $key))) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-8">
                                <input type="text" id="start_date" class="d-inline col-2 mr-1 form-control form-control-sm datepicker start_date" placeholder="{{ date('d-m-Y') }}">
                                <input type="text" id="end_date" class="d-inline col-2 mr-1 form-control form-control-sm datepicker end_date" placeholder="{{ date('d-m-Y') }}">
                                <a href="{{ route('biller.accounts.profit_and_loss', 'v') }}" class="btn btn-info btn-sm search" id="search4">Search</a>
                                <a href="{{ route('biller.accounts.profit_and_loss', 'v') }}" class="btn btn-success btn-sm refresh" id="refresh">
                                    <i class="fa fa-refresh" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <select class="custom-select" id="classlist" data-placeholder="Filter by Class or Sub-class">
                            <option value=""></option>
                            @foreach ($classlists as $item)
                                <option value="{{ $item->id }}" {{ request('classlist_id') == $item->id? 'selected' : '' }}>
                                    {{ $item->name }} {{ $item->parent_class? '('. $item->parent_class->name .')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-8 col-xs-12 ml-auto mr-auto">
                <!-- Income Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-info white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#incomeTab">
                        <span>Income</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show" id="incomeTab">
                        <div class="card-content">
                            <div class="responsive" style="max-height:80vh;overflow-y:auto">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($accountTypeDetails->where('system_rel', 'income') as $detail)
                                            <tr>
                                                <td>{{ $detail->name }}</td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td>
                                                        <span class="ml-3 account-name" data-id="{{ $account->id }}">
                                                            <a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a>
                                                        </span>
                                                    </td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $incomeSum = $accountTypeDetails->where('system_rel', 'income')->sum('balance') @endphp
                                    <td><b>Total Income</b></td>
                                    <td class="font-weight-bold">{{ numberFormat($incomeSum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Cost Of Goods Sold (COGS) Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-red white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#cosTab">
                        <span>Cost of Goods (Opening Stock + Purchases - Closing Stock)</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show" id="cosTab">
                        <div class="card-content">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    @php $cogSum = 0 @endphp
                                    @foreach ($inventoryAccountDetails as $detail)
                                        <tr>
                                            <td>{{ $detail->name }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="ml-3 account-name" data-id="{{ '#' }}">Opening Stock</span>
                                            </td>
                                            <td>{{ numberFormat($openingStockBal) }}</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="ml-3 account-name" data-id="{{ '#' }}">Purchases</span>
                                            </td>
                                            <td>{{ numberFormat($purchasesBal) }}</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="ml-3 account-name" data-id="{{ '#' }}">Closing Stock</span>
                                            </td>
                                            <td>{{ numberFormat($closingStockBal) }}</td>
                                        </tr>            
                                    @endforeach
                                </tbody>
                            </table>                                                  
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $cogSum = round($openingStockBal + $purchasesBal - $closingStockBal, 2) @endphp
                                    <td class="font-weight-bold">Total Cost Of Goods</td>
                                    <td class="font-weight-bold">{{ numberFormat($cogSum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Gross Profit Tab -->
                <div class="card">
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                @php $grossProfit = $incomeSum-$cogSum @endphp
                                <tr>
                                    <td class="font-weight-bold">Gross Profit</td>
                                    <td class="font-weight-bold">{{ numberFormat($grossProfit) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Direct Expense Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-red white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#cosTab">
                        <span>Direct Expenses</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show" id="cosTab">
                        <div class="card-content">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    @foreach ($expenseAccTypeDetails->where('system_rel', 'cogs') as $detail)
                                        <tr>
                                            <td>{{ $detail->name }}</td>
                                            <td></td>
                                        </tr>
                                        @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                            <tr>
                                                <td>
                                                    <span class="ml-3 account-name" data-id="{{ $account->id }}">
                                                        <a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a>
                                                    </span>
                                                </td>
                                                <td>{{ numberFormat($account->balance) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>                                
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $dirExpenseSum = $expenseAccTypeDetails->where('system_rel', 'cogs')->sum('balance') @endphp
                                    <td class="font-weight-bold">Total Direct Expenses</td>
                                    <td class="font-weight-bold">{{ numberFormat($dirExpenseSum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Other Income Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-blue white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#otherIncomeTab">
                        <span>Other Income</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show" id="otherIncomeTab">
                        <div class="card-content">
                            <div class="responsive" style="max-height:80vh;overflow-y:auto">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($accountTypeDetails->where('system_rel', 'other_income') as $detail)
                                            <tr>
                                                <td>{{ $detail->name }}</td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td>
                                                        <span class="ml-3 account-name" data-id="{{ $account->id }}">
                                                            <a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a>
                                                        </span>
                                                    </td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $otherIncomeSum = $accountTypeDetails->where('system_rel', 'other_income')->sum('balance') @endphp
                                    <td class="font-weight-bold">Total Other Income</td>
                                    <td class="font-weight-bold">{{ numberFormat($otherIncomeSum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Indirect Expenses Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-red white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#expenseTab">
                        <span>Indirect Expenses</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show" id="expenseTab">
                        <div class="card-content">
                            <div class="responsive" style="max-height:80vh;overflow-y:auto">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($accountTypeDetails->where('system_rel', 'expense') as $detail)
                                            <tr>
                                                <td>{{ $detail->name }}</td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td>
                                                        <span class="ml-3 account-name" data-id="{{ $account->id }}">
                                                            <a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a>
                                                        </span>
                                                    </td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $indirExpenseSum = $accountTypeDetails->where('system_rel', 'expense')->sum('balance') @endphp
                                    <td class="font-weight-bold">Total Indirect Expenses</td>
                                    <td class="font-weight-bold">{{ numberFormat($indirExpenseSum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Other Expenses Tab -->
                <div class="card">
                    <div class="card-header bg-gradient-x-pink white p-1 h5 font-weight-bold" role="button" data-toggle="collapse" data-target="#otherExpenseTab">
                        <span>Other Expenses</span>
                        <span class="float-right" style="cursor:pointer;">&#9650;&#9660;</span>
                    </div>
                    <div class="collapse multi-collapse show" id="otherExpenseTab">
                        <div class="card-content">
                            <div class="responsive" style="max-height:80vh;overflow-y:auto">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($accountTypeDetails->where('system_rel', 'other_expense') as $detail)
                                            <tr>
                                                <td>{{ $detail->name }}</td>
                                                <td></td>
                                            </tr>
                                            @foreach ($detail->accounts->where('balance', '!=', 0) as $account)
                                                <tr>
                                                    <td>
                                                        <span class="ml-3 account-name" data-id="{{ $account->id }}">
                                                            <a href="{{ route('biller.accounts.show', $account) }}">{{ $account->number }}. {{ $account->holder }}</a>
                                                        </span>
                                                    </td>
                                                    <td>{{ numberFormat($account->balance) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>                                
                            </div>
                        </div>
                    </div>
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                <tr>
                                    @php $otherExpenseSum = $accountTypeDetails->where('system_rel', 'other_expense')->sum('balance') @endphp
                                    <td class="font-weight-bold">Total Other Expense</td>
                                    <td class="font-weight-bold">{{ numberFormat($otherExpenseSum) }}</td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                </div>

                <!-- Net Profit Tab -->
                <div class="card">
                    <div class="card-footer pt-0">
                        <table class="table table-sm mb-0 h5">
                            <tbody>
                                @php $netProfit = $grossProfit + $otherIncomeSum - $dirExpenseSum - $indirExpenseSum - $otherExpenseSum @endphp
                                <tr>
                                    <td class="font-weight-bold">Net Profit</td>
                                    <td class="font-weight-bold">{{ numberFormat($netProfit) }}</td>
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
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});
    // classlist
    $('#classlist').select2({allowClear: true}).change(function() {
        let searchUrl = "{{ route('biller.accounts.profit_and_loss', 'v') }}?";
        let printUrl = "{{ route('biller.accounts.profit_and_loss', 'p') }}?";
        let csvUrl = "{{ route('biller.accounts.profit_and_loss', 'csv') }}?";
        let params = {
            report_period: $('#report-period').val(),
            classlist_id: $('#classlist').val(), 
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val(),
        };
        if (params.classlist_id) {
            const today = "{{ date('d-m-Y') }}";
            if ($('#start_date').val() != $('#end_date').val() && $('#start_date').val() != today && $('#end_date').val() != today) {
                params = {...params, start_date: $('#start_date').val(), end_date: $('#end_date').val()};
            }
            params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
            searchUrl = searchUrl +(new URLSearchParams(params)).toString();
            printUrl = printUrl +(new URLSearchParams(params)).toString();
            csvUrl = "{{ route('biller.accounts.profit_and_loss', 'csv') }}?" + (new URLSearchParams(params)).toString();
        }
        $('#csv').attr('href', csvUrl);
        $('#print').attr('href', printUrl);
        $('#search4').attr('href', searchUrl);
        $('#search4')[0].click();
    });

    // on change date input
    $(document).on('change', 'input', function() {
        let params = {
            report_period: $('#report-period').val(),
            classlist_id: $('#classlist').val(), 
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val()
        };
        params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
        const url = "{{ route('biller.accounts.profit_and_loss', 'v') }}?" +(new URLSearchParams(params)).toString();
        $('#search4').attr('href', url);
    });
    
    $('#csv').click(function(){
        if (dates && dates.length) {
            $('#start_date').datepicker('setDate', new Date(dates[0]));
            $('#end_date').datepicker('setDate', new Date(dates[1]));
            let params = {
                report_period: $('#report-period').val(),
                classlist_id: $('#classlist').val(), 
                start_date: $('#start_date').val(), 
                end_date: $('#end_date').val(),
            };
            params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
            const printUrl = "{{ route('biller.accounts.profit_and_loss', 'csv') }}?" + (new URLSearchParams(params)).toString();
            const searchUrl = "{{ route('biller.accounts.profit_and_loss', 'v') }}?" + (new URLSearchParams(params)).toString();
            $('#csv').attr('href', printUrl);
            $('#search4').attr('href', searchUrl);
        }
    });

    const dates = @json(($dates));
    if (dates && dates.length) {
        $('#start_date').datepicker('setDate', new Date(dates[0]));
        $('#end_date').datepicker('setDate', new Date(dates[1]));
        let params = {
            report_period: $('#report-period').val(),
            classlist_id: $('#classlist').val(), 
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val()
        };
        params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
        const printUrl = "{{ route('biller.accounts.profit_and_loss', 'p') }}?" + (new URLSearchParams(params)).toString();
        const searchUrl = "{{ route('biller.accounts.profit_and_loss', 'v') }}?" + (new URLSearchParams(params)).toString();
        const csvUrl = "{{ route('biller.accounts.profit_and_loss', 'csv') }}?" + (new URLSearchParams(params)).toString();
        $('#csv').attr('href', csvUrl);
        $('#print').attr('href', printUrl);
        $('#search4').attr('href', searchUrl);
    } 
    if ($('#classlist').val()) {
        let params = {
            report_period: $('#report-period').val(),
            classlist_id: $('#classlist').val(),
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val(),
        };
        params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
        const printUrl = "{{ route('biller.accounts.profit_and_loss', 'p') }}?" + (new URLSearchParams(params)).toString();
        const csvUrl = "{{ route('biller.accounts.profit_and_loss', 'csv') }}?" + (new URLSearchParams(params)).toString();
        $('#print').attr('href', printUrl);
        $('#csv').attr('href', csvUrl);
    }

    // Set Report Period
    $('#report-period').change(function() {
        const start = $(this).find(':selected').attr('start');
        const end = $(this).find(':selected').attr('end');
        if (start && end) {
            $('#start_date').val(start).trigger('change');
            $('#end_date').val(end).trigger('change');
            $('#start_date, #end_date').attr('disabled', true);
        } else {
            $('#start_date').val('').trigger('change');
            $('#end_date').val('').trigger('change');
            $('#start_date, #end_date').attr('disabled', false);
        }
    });
    const reportPeriod = @json(request('report_period'));
    if (reportPeriod) $('#report-period').val(reportPeriod).change();
</script>
@endsection