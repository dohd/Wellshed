@extends ('core.layouts.app')

@section ('title', 'Cashbook | ' . trans('labels.backend.accounts.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Cashbook Statement</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    {{-- @include('focus.accounts.partials.accounts-header-buttons') --}}
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row form-group">
                                <div class="col-4">
                                    <label for="account">Account</label>
                                    <select name="account_id" class="custom-select" id="account" data-placeholder="Choose Account">
                                        <option value="">-- Select Account --</option>
                                        @foreach ($accounts as $row)
                                            <option value="{{ $row->id }}">
                                                {{ $row->number }} - {{ $row->holder }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-2">
                                    <label for="tr_type">Transaction Type</label>
                                    <select name="tr_type" id="tr_type" class="custom-select">
                                        <option value="">-- Select Type --</option>
                                        @foreach (['receipt', 'payment'] as $val)
                                            <option value="{{ $val }}">{{ ucfirst($val) }}</option>
                                        @endforeach
                                    </select>
                                </div>  

                                <div class="col-2">
                                    <label for="debit">Debit (Cash In)</label>
                                    {{ Form::text('debit', null, ['class' => 'form-control', 'id' => 'debit', 'readonly']) }}
                                </div>
                                <div class="col-2">
                                    <label for="credit">Credit (Cash Out)</label>
                                    {{ Form::text('credit', null, ['class' => 'form-control', 'id' => 'credit', 'readonly']) }}
                                </div>
                                <div class="col-2">
                                    <label for="balance">Balance</label>
                                    {{ Form::text('balance', null, ['class' => 'form-control', 'id' => 'balance', 'readonly']) }}
                                </div>  
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-2">{{ trans('general.search_date')}}</div>
                                @php
                                    $now = date('d-m-Y');
                                    $start = date('d-m-Y', strtotime("{$now} - 3 months"));
                                @endphp
                                <div class="col-2">
                                    <input type="text" name="start_date" value="{{ $start }}" id="start_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-2">
                                    <input type="text" name="end_date" value="{{ $now }}" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                </div>
                            </div>
                            <hr>   
                            <table id="cashbookTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tr No.</th>
                                        <th>Date</th>  
                                        <th>Particulars</th>
                                        <th>Tr Type</th>
                                        <th>Account</th>
                                        <th>Debit (Cash In)</th>
                                        <th>Credit (Cash Out)</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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
    config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);

            this.drawDataTable();

            $('#search').click(this.dateSearchClick);
            $('#account').change(this.accountChange);
            $('#tr_type').change(this.trTypeChange);
        },

        accountChange() {
            Index.resetDataTable();
        },

        trTypeChange() {
            Index.resetDataTable();
        },

        dateSearchClick() {
            Index.resetDataTable();
        },

        resetDataTable() {
            $('#cashbookTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#cashbookTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                ajax: {
                    url: "{{ route('biller.accounts.get_cashbook') }}",
                    type: 'post',
                    data: {
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                        account_id: $('#account').val(),
                        tr_type: $('#tr_type').val(),
                    },
                    dataSrc: ({data}) => {
                        if (data.length) {
                            const aggr = data[0].aggregate;
                            $('#debit').val(aggr.sum_debit);
                            $('#credit').val(aggr.sum_credit);
                            $('#balance').val(aggr.balance);
                        }
                        return data;
                    },
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    ...[
                        'tid', 'tr_date', 'note', 'tr_type', 'account', 'debit', 'credit'
                    ].map(v => ({name: v, data: v})),
                ],
                order: [
                    [0, "desc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(() => Index.init());
</script>
@endsection