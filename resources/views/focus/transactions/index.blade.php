@extends('core.layouts.app')
@section('title', 'Transactions Management')

@if ($words)
    @php
        $transactions = $segment->transactions;
        $debit = $transactions->sum('debit');
        $credit = $transactions->sum('credit');
        if (@$segment->currency->rate != 1) {
            $debit = $transactions->sum('fx_debit');
            $credit = $transactions->sum('fx_credit');
        } 

        $expenseProjects = $segment->transactions->where('bill_id', '>', 0)
            ->pluck('project')->filter()->unique('id')
            ->map(fn($v) => '<a href="'.route('biller.projects.show', $v).'">'. gen4tid('PRJ-', $v->tid) .'</a>');
        $saleProjects = $segment->transactions->where('invoice_id', '>', 0)
            ->pluck('project')->filter()->unique('id')
            ->map(fn($v) => '<a href="'.route('biller.projects.show', $v).'">'. gen4tid('PRJ-', $v->tid) .'</a>');
        $diff = array_diff($expenseProjects->toArray(), $saleProjects->toArray());
        $diffCount = count($diff);

        $model_details = [
            'tr_category' => [trans('general.description') => $segment->note],
            'customer' => [trans('customers.email') => $segment->email],
            'account' => [
                'Account No' => $segment->number, 
                $words['name'] => $words['name_data'],
                'Description' => $segment->note,
                'Account Type' => $segment->account_type, 
                'Account Detail Type' => @$segment->account_type_detail->name,
                "Project Difference ({$diffCount})" => implode(', ', $diff),
                'Currency' => @$segment->currency->code,
                'Is Sub-account' => $segment->is_sub_account? 'True' : 'False',
                'Is Manual Journal Account' => $segment->is_manual_journal? 'True' : 'False',
                'Beginning As Of' => $segment->opening_balance_date? dateFormat($segment->opening_balance_date) : '',
                'Beginning Balance' => numberFormat($segment->opening_balance),
            ],
        ];        

        $rows = [];
        if ($input['rel_type'] == 0) $rows = $model_details['tr_category']; 
        elseif ($input['rel_type'] < 9) $rows = $model_details['customer'];
        elseif ($input['rel_type'] == 9) $rows = $model_details['account'];

        $isFx = @$segment->currency->rate && +$segment->currency->rate != 1;
    @endphp
@endif

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Transactions Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.transactions.partials.transactions-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <!-- Account Summary -->
    @if ($words)
        <div class="card">
            <div class="card-body">
                <h5>Ledger Account</h5>
                <div class="row">
                    <div class="col-6">
                        @php
                            $exempted = ['Currency', 'Is Sub-account', 'Is Manual Journal Account', 'Beginning As Of', 'Beginning Balance'];
                        @endphp
                        <table class="table table-sm table-bordered">
                            <tbody>
                                @foreach ($rows as $key => $val)
                                    @if (!in_array($key, $exempted))
                                        <tr>
                                            <th>{{ $key }}</th>
                                            <td>{!! $val !!} </td>
                                        </tr> 
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-sm table-bordered">
                            <tbody>
                                @foreach ($rows as $key => $val)
                                    @if (in_array($key, $exempted))
                                        <tr>
                                            <th>{{ $key }}</th>
                                            <td>{!! $val !!} </td>
                                        </tr> 
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- End Account Summary -->

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row no-gutters">
                                @php
                                    $now = date('d-m-Y');
                                    $start = date('d-m-Y', strtotime("{$now} - 3 months"));
                                @endphp
                                <div class="col-md-2">{{ trans('general.search_date')}}:</div>
                                <div class="col-md-1 mr-1">
                                    <input type="text" name="start_date" value="{{ $start }}" id="start_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-1 mr-1">
                                    <input type="text" name="end_date" value="{{ $now }}" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-1">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                </div>
                            </div>
                            <hr> 
                            <table id="transactionsTbl" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th> 
                                        <th class="th-date">Date</th>
                                        <th>Type</th>
                                        <th>{{ $is_tax? 'Customer PIN' : 'Account Name' }}</th>
                                        @if (request('system') == 'receivable')
                                            <th>Payer</th>
                                        @elseif (request('system') == 'payable')
                                            <th>Payee</th>
                                        @elseif (in_array(request('system'), ['wip', 'bank']))
                                            <th>Payer</th>
                                            <th>Payee</th>
                                        @endif 
                                        @if (in_array(request('system'), ['wip', 'cog']))
                                            <th>Project</th>
                                        @endif  
                                        <th>Note</th>
                                        @if ($is_tax)
                                            <th>VAT %</th>
                                            <th>VAT Amount</th>   
                                        @endif
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Amount (FCY)</th>
                                        <th>CY. Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1">
                                            <i class="fa fa-spinner spinner"></i>
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
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Index = {    
        startDate: '',
        endDate: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);

            Index.drawDataTable();
            $('#search').click(Index.dateSearchClick);
            $('#start_date, #end_date').change(Index.onDateChange)
        },

        dateSearchClick() {
            Index.startDate = $('#start_date').val();
            Index.endDate = $('#end_date').val();
            $('#transactionsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        onDateChange() {
            Index.startDate = $('#start_date').val();
            Index.endDate = $('#end_date').val();
        },

        drawDataTable() {
            const system = @json(request('system'));
            const input = @json(@$input);
            $('#transactionsTbl').dataTable({
                processing: true,
                // serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.transactions.get") }}',
                    type: 'post',
                    data: {system, start_date: Index.startDate, end_date: Index.endDate, ...input},
                    // dataSrc: ({data}) => {
                    //     $('.tbl_debit').val('');
                    //     $('.tbl_credit').val('');
                    //     $('.tbl_balance').val('');
                    //     if (data.length && data[data.length-1].aggregate) {
                    //         const aggr = data[data.length-1].aggregate;
                    //         $('.tbl_debit').val(aggr.debit);
                    //         $('.tbl_credit').val(aggr.credit);
                    //         $('.tbl_balance').val(aggr.balance);
                    //     }
                    //     return data;
                    // },
                },
                columns: [
                    {data: 'DT_Row_Index',name: 'id'},
                    ...[
                        'tr_date',
                        'tr_type', 
                        'reference', 
                        @if (request('system') == 'receivable')
                            'payer',
                        @elseif (request('system') == 'payable')
                            'payee',
                        @elseif (in_array(request('system'), ['wip', 'bank']))
                            'payer', 
                            'payee',
                        @endif
                        @if (in_array(request('system'), ['wip', 'cog']))
                            'project',
                        @endif
                        'note', 
                        @if (request('system') == 'tax')
                            'vat_rate', 
                            'vat_amount',
                        @endif
                        'debit',
                        'credit',
                        'fx_amount',
                        'fx_curr_rate',
                    ].map(v => ({data: v, name: v})),
                    // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                columnDefs: [
                    @if (in_array(request('system'), ['receivable', 'payable']))
                        { type: "custom-number-sort", targets: [6,7,8] },
                        { type: "custom-date-sort", targets: 1 }
                    @elseif (request('system') == 'bank')
                        { type: "custom-number-sort", targets: [7,8,9] },
                        { type: "custom-date-sort", targets: 1 }
                    @else
                        { type: "custom-number-sort", targets: [5,6,7] },
                        { type: "custom-date-sort", targets: 1 }
                    @endif
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
        },
    };

    $(Index.init);
</script>
@endsection