@extends ('core.layouts.app')
@section ('title', 'Cash Flow Statement | ' . trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-9">
            <h3> 
                <span class="mr-1">Cash Flow Statement</span>
                <a class="btn btn-success btn-sm" href="{{ route('biller.accounts.cash_flow_statement', 'p') }}" target="_blank" id="print">
                    <i class="fa fa-print"></i> {{ trans('general.print') }}
                </a>
            </h3>
        </div>
        <div class="content-header-right col-3">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.accounts.partials.accounts-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row mb-1">
            <div class="col-8">
                <span class="col-5 d-inline">Period Between</span>
                <input type="text" id="start_date" class="d-inline col-2 mr-1 form-control form-control-sm datepicker start_date">
                <input type="text" id="end_date" class="d-inline col-2 mr-1 form-control form-control-sm datepicker end_date">
                <a href="{{ route('biller.accounts.cash_flow_statement', 'v') }}" class="btn btn-info btn-sm search" id="search4">Search</a>
                <a href="{{ route('biller.accounts.cash_flow_statement', 'v') }}" class="btn btn-success btn-sm refresh" id="refresh">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                @php
                    $operating_sum = $net_income;
                    $investing_sum = 0;
                    $financing_sum = 0;
                @endphp

                <!-- Operating Activities -->
                <div class="card">
                    <div class="card-content print_me">
                        <h5 class="title bg-gradient-x-info p-1 white">Operating Activities</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account No</th>
                                    <th>Account</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>Net Income</td>
                                    <td><h5>{{ numberFormat($net_income) }}</h5></td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="2" class="text-center"><i>Adjustments to reconcile Net Income</i></td>
                                </tr>
                                @foreach ($operating_accounts as $i => $account)
                                    @php
                                        $q = $account->transactions()->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates));
                                        $system = @$account->account_type_detail->system;
                                        $account_type = $account->account_type;
                                        $neg_prefix = false;
                                        $balance = 0;
                                        // non-cash adjustment
                                        $ajustment_types = ['depreciation_expense', 'amortization_expense', 'asset_sale_gain', 'asset_sale_loss'];
                                        if (in_array($system, $ajustment_types)) {
                                            $q1 = clone $q;
                                            if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q1->sum(DB::raw('debit - credit'));
                                            else $balance = $q1->sum(DB::raw('credit - debit'));
                                            $balance = round($balance, 2);
                                            // add expenses while subtract revenue
                                            if (in_array($system, ['depreciation_expense', 'amortization_expense', 'asset_sale_loss'])) $operating_sum += $balance;
                                            elseif ($system == 'asset_sale_gain') $operating_sum -= $balance;
                                            if ($system == 'asset_sale_gain') $neg_prefix = true;
                                        }
                                        // changes in working capital
                                        $ajustment_types = ['receivable', 'payable', 'inventory_asset', 'prepaid_expenses', 'accrued_liability', 'payroll_liability', 'credit_card'];
                                        if (in_array($system, $ajustment_types)) {
                                            $q2 = clone $q;
                                            if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q2->sum(DB::raw('debit - credit'));
                                            else $balance = $q2->sum(DB::raw('credit - debit'));
                                            $balance = round($balance, 2);
                                            // subtract increase in asset else add decrease in asset
                                            if ($account_type == 'Asset') {
                                                if ($balance > 0) $operating_sum -= $balance;
                                                else $operating_sum += $balance;
                                                if ($balance > 0) $neg_prefix = true;
                                            } 
                                            // add increase in liability else subtract decrease in asset
                                            if ($account_type == 'Liability') {
                                                if ($balance > 0) $operating_sum += $balance;
                                                else $operating_sum -= $balance;
                                                if ($balance < 0) $neg_prefix = true;
                                            }                                         
                                        }
                                        // other operating cash flows (interests and taxes)
                                        $ajustment_types = ['interest_expense', 'sale_tax_payable', 'vat_payable'];
                                        if (in_array($system, $ajustment_types)) {
                                            $q3 = clone $q;
                                            if ($account_type == 'Expense') $balance = $q3->sum(DB::raw('debit - credit'));
                                            $balance = round($balance, 2);
                                            // subtract expenses
                                            $operating_sum -= $balance;
                                            $neg_prefix = true;
                                        }
                                        if (!$balance) continue;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $account->number }}</td>
                                        <td>{{ $account->holder }}</td>
                                        <td>{{ $neg_prefix? '(-)' : '' }} {{ numberFormat($balance) }}</td>
                                    </tr>
                                @endforeach
                                <tr style="border-top: 2px solid grey;">
                                    <td colspan="3"></td>
                                    <td><h3>{{ amountFormat($operating_sum) }}</h3></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>        
                </div>

                <!-- Investing Activities -->
                <div class="card">
                    <div class="card-content print_me">
                        <h5 class="title bg-gradient-x-purple p-1 white">Investing Activities</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account No</th>
                                    <th>Account</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($investing_accounts as $i => $account)
                                    @php
                                        $q = $account->transactions()->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates));
                                        $balance = $q->sum(DB::raw('debit - credit'));
                                        $balance = round($balance, 2);
                                        $investing_sum += $balance;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $account->number }}</td>
                                        <td>{{ $account->holder }}</td>
                                        <td>{{ numberFormat($balance) }}</td>
                                    </tr>
                                @endforeach
                                <tr style="border-top: 2px solid grey;">
                                    <td colspan="3"></td>
                                    <td><h3>{{ amountFormat($investing_sum) }}</h3></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>        
                </div>

                <!-- Financing Activities -->
                <div class="card">
                    <div class="card-content print_me">
                        <h5 class="title bg-gradient-x-grey-blue p-1 white">Financing Activities</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account No</th>
                                    <th>Account</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($financing_accounts as $i => $account)
                                    @php
                                        $q = $account->transactions()->when(@$dates, fn($q) => $q->whereBetween('tr_date', $dates));
                                        $system = @$account->account_type_detail->system;
                                        $account_type = $account->account_type;
                                        $neg_prefix = false;
                                        $balance = 0;
                                        // cash in adjustment
                                        $ajustment_types = ['common_stock', 'preferred_stock', 'bonds_payable', 'notes_payable', 'loan_payable'];
                                        if (in_array($system, $ajustment_types)) {
                                            $q1 = clone $q;
                                            if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q1->sum(DB::raw('debit - credit'));
                                            else $balance = $q1->sum(DB::raw('credit - debit'));
                                            $balance = round($balance, 2);
                                            // add cash in
                                            $financing_sum += $balance;
                                            if ($system == 'asset_sale_gain') $neg_prefix = true;
                                        }
                                        // cash out adjustment
                                        $ajustment_types = ['treasury_stock', 'dividends_payable',];
                                        if (in_array($system, $ajustment_types)) {
                                            $q2 = clone $q;
                                            if (in_array($account_type, ['Asset', 'Expense'])) $balance = $q1->sum(DB::raw('debit - credit'));
                                            else $balance = $q2->sum(DB::raw('credit - debit'));
                                            $balance = round($balance, 2);
                                            // subtract cash out
                                            $financing_sum -= $balance;
                                            if ($system == 'asset_sale_gain') $neg_prefix = true;
                                        }                                        
                                        if (!$balance) continue;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $account->number }}</td>
                                        <td>{{ $account->holder }}</td>
                                        <td>{{ numberFormat($balance) }}</td>
                                    </tr>
                                @endforeach
                                <tr style="border-top: 2px solid grey;">
                                    <td colspan="3"></td>
                                    <td><h3>{{ amountFormat($financing_sum) }}</h3></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>        
                </div>
                
                <!-- Summary -->
                <div class="card">
                    <div class="card-content">
                        <h5 class="title bg-gradient-x-danger p-1 white">Summary</h5>
                        <table class="table table-sm">
                            <tbody>
                                @php 
                                    $net_increase = $operating_sum + $investing_sum + $financing_sum;
                                    $ending_cash = $net_increase + $beginning_cash;
                                @endphp
                                <tr>
                                    
                                    <td><i>Net Cash Increase</i></td>
                                    <td><h5>{{ amountFormat($net_increase) }}</h5></td>
                                </tr>
                                <tr>
                                    
                                    <td>Beginning Cash</td>
                                    <td><h5>{{ amountFormat($beginning_cash) }}</h5></td>
                                </tr>
                                <tr style="border-top: 2px solid grey;">
                                    
                                    <td>Ending Cash</td>
                                    <td><h5>{{ amountFormat($ending_cash) }}</h5></td>
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
    // datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true}).datepicker('setDate', new Date());
    
    const dates = @json((@$date_range));
    if (dates && dates.length) {
        $('#start_date').datepicker('setDate', new Date(dates[0]));
        $('#end_date').datepicker('setDate', new Date(dates[1]));
        const queryStr = '?start_date=' + $('#start_date').val() + '&end_date=' + $('#end_date').val();
        const printUrl = "{{ route('biller.accounts.cash_flow_statement', 'p') }}" + queryStr;
        $('#print').attr('href', printUrl);

        const searchUrl = "{{ route('biller.accounts.cash_flow_statement', 'v') }}" + queryStr;
        $('#search4').attr('href', searchUrl);
    } 

    // filter by date
    $(document).on('change', 'input', function() {
        const queryStr = '?start_date=' + $('#start_date').val() + '&end_date=' + $('#end_date').val();
        const url = "{{ route('biller.accounts.cash_flow_statement', 'v') }}" + queryStr;
        $('#search4').attr('href', url);
    });
</script>
@endsection