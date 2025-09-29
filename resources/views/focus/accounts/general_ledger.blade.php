@extends ('core.layouts.app')
@section ('title', 'General Ledger | Accounting Reports')

@section('content')
<style>
    .card {
        margin-bottom: 1rem!important;
    }
    .card-content {
        padding-left: 1rem!important;
        padding-right: 1rem!important;
    }
    .card-footer td {
        border-top: none;
        padding-left: 5px!important;
        padding-right: 5px!important;
    }
    .card-footer td:nth-child(2) {
        padding-left: 2rem!important;
    }
</style>
<div class="content-wrapper pt-1">
    <div class="content-header row mb-1">
        <div class="content-header-left col-12">
            <h4 class="content-header-title font-weight-bold text-center">General Ledger</h4>
        </div>
    </div>

    <div class="content-body">
        <!-- Filters -->
        <div class="row">
            <div class="col-10 col-xs-12 ml-auto mr-auto">
                <div class="btn-group mb-1">
                    <a href="{{ route('biller.accounts.general_ledger', 'csv') }}" class="btn btn-purple btn-sm" target="_blank" id="csv">
                        <i class="fa fa-print"></i> CSV Export
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-content pt-1">
                        <div class="row mb-1">
                            <div class="col-3">
                                <div class="form-group">
                                    <select class="custom-select" id="classlist" data-placeholder="Filter by Class or Sub-class">
                                        <option value=""></option>
                                        @foreach ($classlists as $item)
                                            <option value="{{ $item->id }}" {{ request('classlist_id') == $item->id? 'selected' : '' }}>
                                                {{ $item->name }} {{ $item->parent_class? "({$item->parent_class->name})" : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <select class="custom-select" id="tr_type" data-placeholder="Filter by Transaction Type" multiple>
                                        @php
                                            $types = [
                                                'bill' => 'Bill',
                                                'pmt' => 'Bill Payment',
                                                'inv' => 'Sale',
                                                'dep' => 'Receive Payment',
                                                'cnote' => 'Credit Note',
                                                'dnote' => 'Debit Note',
                                                'wht' => 'Tax Withholding',
                                                'chrg' => 'Charge',
                                                'grn' => 'Goods Receive Note',
                                                // '' => 'Stock Return',
                                                'xfer' => 'Transfer',
                                                'genjr' => 'Journal Entry',
                                                'stock' => 'Stock Adjustment',
                                                // 'Opening Stock',
                                            ];
                                        @endphp
                                        @foreach ($types as $key => $type)
                                            <option value="{{ $key }}" {{ in_array($key, $trType)? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <select class="custom-select" id="account" data-placeholder="Filter by Account" multiple>
                                        @foreach ($selectAccounts as $account)
                                            <option value="{{ $account->id }}" {{ in_array($account->id, $accountId)? 'selected' : '' }}>
                                                {{ $account->number }}: {{ $account->holder }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Filters -->

        <div class="row">
            <div class="col-10 col-xs-12 ml-auto mr-auto">
                <div class="card">
                    <div class="card-content pt-1">
                        <div class="row">
                            <div class="col-7">
                                <h4 class="col-5 d-inline">Report Period</h4>
                                <input type="text" id="start_date" class="d-inline col-2 mr-1 form-control form-control-sm datepicker start_date" placeholder="{{ date('d-m-Y') }}">
                                <input type="text" id="end_date" class="d-inline col-2 mr-1 form-control form-control-sm datepicker end_date" placeholder="{{ date('d-m-Y') }}">
                                <a href="{{ route('biller.accounts.general_ledger') }}" class="btn btn-info btn-sm search" id="search4">Search</a>
                                <a href="{{ route('biller.accounts.general_ledger') }}" class="btn btn-success btn-sm refresh" id="refresh">
                                    <i class="fa fa-refresh" aria-hidden="true"></i> Refresh
                                </a>
                            </div>
                            <div class="col-2 ml-auto">
                                <select class="custom-select" id="amount_type" style="height:30px;">
                                    <option value="">-- Amount Type --</option>
                                    @foreach (['Debit', 'Credit'] as $item)
                                        <option value="{{ $item }}" {{ request('amount_type') == $item? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-7">
                                <h4 class="col-5 d-inline">Updated At</h4>
                                <input type="text" id="start_date_mod" class="d-inline col-2 mr-1 form-control form-control-sm datepicker start_date_mod" placeholder="{{ date('d-m-Y') }}">
                                <input type="text" id="end_date_mod" class="d-inline col-2 mr-1 form-control form-control-sm datepicker end_date_mod" placeholder="{{ date('d-m-Y') }}">
                            </div>
                        </div>
                        <hr>
                        <!-- Table -->
                        <div class="responsive" style="max-height:100vh;overflow-y:auto">
                            <table class="table table-sm" width="100%">
                                <thead>
                                    <tr class="bg-gradient-x-info white">
                                        <th width="25%">Type</th>
                                        <th width="5%">Date</th>
                                        <th>Ref No.</th>
                                        <th width="20%">Payee</th>
                                        <th width="30%">Note</th>
                                        <th width="5%">Split</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody style="display: none;">
                                    @foreach ($accounts as $account)
                                        @php
                                            $accountBalance = $dates? $account->balance : 0;
                                            $movingBalance = $accountBalance;
                                            $totalAmount = 0;
                                        @endphp
                                        <tr>
                                            <td class="font-weight-bold">{{ $account->number }}: {{ $account->holder }}</td>
                                            @foreach (range(1,6) as $item)
                                                <td>&nbsp;</td>
                                            @endforeach
                                            <td class="text-center font-weight-bold">{{ numberFormat($accountBalance) }}</td>
                                        </tr>
                                        @foreach ($account->transactions as $tr)
                                            @php
                                                if (in_array($account->account_type, ['Asset', 'Expense'])) {
                                                    $amount = $tr->debit > 0? +$tr->debit : -$tr->credit;
                                                } else $amount = $tr->credit > 0? +$tr->credit : -$tr->debit;
                                                $movingBalance += $amount;
                                                $totalAmount += $amount;

                                                $customer = @$tr->customer->company ?: @$tr->customer->name;
                                                $supplier = @$tr->supplier->name ?: @$tr->supplier->company;
                                                $id = '';
                                                $type = '';
                                                $tid = '';
                                                if ($tr->bill) {
                                                    $id = $tr->bill->id;
                                                    $tid = $tr->bill->tid;
                                                    $tid = '<a href="'.route('biller.utility_bills.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Bill';
                                                } elseif ($tr->bill_payment) {
                                                    $id = $tr->bill_payment->id;
                                                    $tid = $tr->bill_payment->tid;
                                                    $tid = '<a href="'.route('biller.billpayments.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Bill Payment';
                                                } elseif ($tr->invoice) {
                                                    $id = $tr->invoice->id;
                                                    $tid = $tr->invoice->tid;
                                                    $tid = '<a href="'.route('biller.invoices.edit_project_invoice', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Sale';
                                                } elseif ($tr->invoice_payment) {
                                                    $id = $tr->invoice_payment->id;
                                                    $tid = $tr->invoice_payment->tid;
                                                    $tid = '<a href="'.route('biller.invoice_payments.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Receive Payment';
                                                } elseif ($tr->creditnote) {
                                                    $id = $tr->creditnote->id;
                                                    $tid = $tr->creditnote->tid;
                                                    $tid = '<a href="'.route('biller.creditnotes.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Credit Note';
                                                } elseif ($tr->debitnote) {
                                                    $id = $tr->debitnote->id;
                                                    $tid = $tr->debitnote->tid;
                                                    $tid = '<a href="'.route('biller.creditnotes.edit', $id).'?is_debit=1">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Debit Note';
                                                } elseif ($tr->withholding) {
                                                    $id = $tr->withholding->id;
                                                    $tid = $tr->withholding->tid;
                                                    $tid = '<a href="'.route('biller.withholdings.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Tax Withholding';
                                                } elseif ($tr->charge) {
                                                    $id = $tr->charge->id;
                                                    $tid = $tr->charge->tid;
                                                    $tid = '<a href="'.route('biller.charges.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Charge';
                                                } elseif ($tr->grn) {
                                                    $id = $tr->grn->id;
                                                    $tid = $tr->grn->tid;
                                                    $tid = '<a href="'.route('biller.goodsreceivenote.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Goods Receive Note';
                                                } elseif ($tr->stock_issue) {
                                                    $id = $tr->stock_issue->id;
                                                    $tid = $tr->stock_issue->tid;
                                                    $tid = '<a href="'.route('biller.stock_issues.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Stock Issue';
                                                } elseif ($tr->sale_return) {
                                                    $id = $tr->sale_return->id;
                                                    $tid = $tr->sale_return->tid;
                                                    $tid = '<a href="'.route('biller.sale_returns.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Stock Return';
                                                } elseif ($tr->transfer) {
                                                    $id = $tr->transfer->id;
                                                    $tid = $tr->transfer->tid;
                                                    $tid = '<a href="'.route('biller.banktransfers.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Transfer';
                                                } elseif ($tr->manualjournal) {
                                                    $id = $tr->manualjournal->id;
                                                    $tid = $tr->manualjournal->tid;
                                                    $tid = '<a href="'.route('biller.journals.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Journal Entry';
                                                } elseif ($tr->stock_adj) {
                                                    $id = $tr->stock_adj->id;
                                                    // $tid = $tr->manualjournal->tid;
                                                    // $tid = '<a href="'.route('biller.journals.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Stock Adjustment';
                                                } elseif ($tr->opening_stock) {
                                                    $id = $tr->opening_stock->id;
                                                    // $tid = $tr->manualjournal->tid;
                                                    // $tid = '<a href="'.route('biller.journals.edit', $id).'">'. gen4tid('', $tid) .'</a>';
                                                    $type = 'Opening Stock';
                                                }
                                            @endphp
                                            <tr>
                                                <td class="pl-3">{{ $type }}</td>
                                                <td>{{ dateFormat($tr->tr_date) }}</td>
                                                <td class="text-center">{!! $tid !!}</td>
                                                <td>{{ $customer ?: $supplier }}</td>
                                                <td>{{ $tr->note }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-info btn-sm journal-btn" data-id="{{ $tr->id }}" data-toggle="modal" data-target="#journalModal">
                                                        -SPLIT-
                                                    </button>
                                                </td>
                                                <td class="text-center">{{ numberFormat($amount) }}</td>
                                                <td class="text-center">{{ numberFormat($movingBalance) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td>Total {{ $account->holder }}</td>
                                            @foreach(range(1,5) as $i)
                                                <td></td>
                                            @endforeach
                                            <td class="text-center font-weight-bold">{{ numberFormat($totalAmount) }}</td>
                                            <td class="text-center font-weight-bold">{{ numberFormat($movingBalance) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <script type="text/javascript">
                                setTimeout(() => {
                                    document.getElementsByTagName('tbody')[0].style.removeProperty('display');
                                }, 500);
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.accounts.partials.journal_modal')
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    $.ajaxSetup(config.ajax);
    $('.datepicker').datepicker(config.date);
    // select2 
    $('#classlist').select2({allowClear: true});
    $('#account').select2({allowClear: true});
    $('#tr_type').select2({allowClear: true});

    // modal show
    const defaultModalRow = $('#journal-tbl tbody tr:first').clone();
    $('.journal-btn').click(function(e) {
        // e.stopPropagation();
        const transaction_id = $(this).attr('data-id');
        const url = "{{ route('biller.accounts.journal_entries') }}";
        $.post(url, {transaction_id})
        .then(data => {
            if (!data.length) return;
            $('#journal-tbl tbody').html('')
            let debits = 0, credits = 0;
            data.forEach(v => {
                debits += v.debit;
                credits += v.credit;
                $('#journal-tbl tbody').append(`<tr>
                    <th>${v.number}: ${v.holder}</th>
                    <td>${v.payee || ''}</td>
                    <td>${accounting.formatNumber(v.debit)}</td>
                    <td>${accounting.formatNumber(v.credit)}</td>
                    </tr>`
                );
            });
            $('#journal-tbl tfoot tr th:eq(2)').html(accounting.formatNumber(debits));
            $('#journal-tbl tfoot tr th:eq(3)').html(accounting.formatNumber(credits));
        })
        .fail((xhr, status, error) => console.log(error));
    });

    $(document).on('click', '#search4', function(e) {
        const isPeriod = !$('.start_date').val() && !$('.end_date').val();
        const isUpdatedAt = !$('.start_date_mod').val() && !$('.end_date_mod').val();
        if (isPeriod && isUpdatedAt) {
            e.preventDefault();
            swal({
                title: 'Validation Error!',
                text: "Report period date range required",
                icon: "error",
                dangerMode: true,
            });
        }
    });

    // filter change
    $('#classlist, #account, #tr_type, #amount_type, #start_date, #end_date, #start_date_mod, #end_date_mod')
    .on('change', function() {
        let searchUrl = "{{ route('biller.accounts.general_ledger') }}?";
        let printUrl = "{{ route('biller.accounts.general_ledger', 'p') }}?";
        let csvUrl = "{{ route('biller.accounts.general_ledger', 'csv') }}?";
        let params = {
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val(),
            start_date_mod: $('#start_date_mod').val(), 
            end_date_mod: $('#end_date_mod').val(),
            classlist_id: $('#classlist').val(), 
            amount_type: $('#amount_type').val(), 
            account_id: Array.isArray($('#account').val())? $('#account').val().join(',') : $('#account').val(), 
            tr_type: Array.isArray($('#tr_type').val())? $('#tr_type').val().join(',') : $('#tr_type').val(), 
        };
        params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
        searchUrl = searchUrl +(new URLSearchParams(params)).toString();
        printUrl = printUrl +(new URLSearchParams(params)).toString();
        csvUrl = "{{ route('biller.accounts.general_ledger', 'csv') }}?" + (new URLSearchParams(params)).toString();
        
        $('#search4').attr('href', searchUrl).attr('tital', 'Visit: ' + searchUrl);
        $('#print').attr('href', printUrl);
        $('#csv').attr('href', csvUrl);
        // $('#search4')[0].click();
    });

    const dates = @json(($dates));
    if (dates && dates.length) {
        $('#start_date').datepicker('setDate', new Date(dates[0]));
        $('#end_date').datepicker('setDate', new Date(dates[1]));
        let params = {
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val(),
            start_date_mod: $('#start_date_mod').val(), 
            end_date_mod: $('#end_date_mod').val(),
            classlist_id: $('#classlist').val(), 
            amount_type: $('#amount_type').val(), 
            account_id: Array.isArray($('#account').val())? $('#account').val().join(',') : $('#account').val(), 
            tr_type: Array.isArray($('#tr_type').val())? $('#tr_type').val().join(',') : $('#tr_type').val(), 
        };
        params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
        const searchUrl = "{{ route('biller.accounts.general_ledger') }}?" + (new URLSearchParams(params)).toString();
        params.type = 'csv';
        const csvUrl = "{{ route('biller.accounts.general_ledger') }}?" + (new URLSearchParams(params)).toString();
        $('#csv').attr('href', csvUrl);
        $('#search4').attr('href', searchUrl);
    }
    // CSV click
    $('#csv').click(function(){
        if (dates && dates.length) {
            $('#start_date').datepicker('setDate', new Date(dates[0]));
            $('#end_date').datepicker('setDate', new Date(dates[1]));
            let params = {
                start_date: $('#start_date').val(), 
                end_date: $('#end_date').val(),
                start_date_mod: $('#start_date_mod').val(), 
                end_date_mod: $('#end_date_mod').val(),
                classlist_id: $('#classlist').val(), 
                amount_type: $('#amount_type').val(), 
                account_id: Array.isArray($('#account').val())? $('#account').val().join(',') : $('#account').val(), 
                tr_type: Array.isArray($('#tr_type').val())? $('#tr_type').val().join(',') : $('#tr_type').val(), 
            };
            params = Object.fromEntries(Object.entries(params).filter(([key, value]) => value));
            const searchUrl = "{{ route('biller.accounts.general_ledger') }}?" + (new URLSearchParams(params)).toString();
            params.type = 'csv';
            const csvUrl = "{{ route('biller.accounts.general_ledger') }}?" + (new URLSearchParams(params)).toString();
            $('#csv').attr('href', csvUrl);
            $('#search4').attr('href', searchUrl);
        }
    });
</script>
@endsection