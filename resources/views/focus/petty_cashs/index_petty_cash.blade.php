@extends ('core.layouts.app')

@section ('title', 'Petty Cash Report')

@section('page-header')
    <h1>{{ 'Petty Cash Report' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Petty Cash Management' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.petty_cashs.partials.petty_cashs-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-header">
                                    <div class="row form-group">
                                        <div class="col-4">
                                            <label for="user_type">Type of User</label>
                                             <select name="user_type" id="user_type" class="round form-control">
                                                <option value="">--select user type--</option>
                                                <option value="employee">Employee</option>
                                                <option value="casual">Casual Labourer</option>
                                                <option value="third_party_user">Third Party User</option>
                                            </select>
                                        </div>
                                        <div class="col-4 div_employee">
                                            <label for="employee">Search Employee</label>
                                            <select name="employee_id" id="employee" class="form-control" data-placeholder="Search Employee">
                                                <option value="">--Search Employee--</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}">{{ $employee->fullname }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4 div_casual d-none">
                                            <label for="casual">Search Casual Labourer</label>
                                            <select name="casual_id" id="casual" class="form-control" data-placeholder="Search Casual Labourer">
                                                <option value="">--Search Casual Labourer--</option>
                                                @foreach ($casuals as $casual)
                                                    <option value="{{ $casual->id }}">{{ $casual->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4 div_third_party_user d-none">
                                            <label for="third_party_user">Search Third Party User</label>
                                            <select name="third_party_user_id" id="third_party_user" class="form-control" data-placeholder="Search Third Party User">
                                                <option value="">Search Third Party User</option>
                                                @foreach ($third_party_users as $third_party_user)
                                                    <option value="{{ $third_party_user->id }}">{{ $third_party_user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-4">
                                            <label for="money_transfer">Money Transfer</label><br>
                                            <span class="money_transfer">0.00</span>
                                            <input type="hidden" class="transfer_amt" value="0">
                                        </div>
                                        <div class="col-4">
                                            <label for="bill_payment_amount">Bill Payment Amount</label><br>
                                            <span class="bill_payment_amount">0.00</span>
                                            <input type="hidden" class="bill_pay_amt" id="amount_total" value="0">
                                        </div>
                                        <div class="col-4">
                                            <label for="balance">Balance</label><br>
                                            <span class="balance">0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Petty Cash</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Bill Payment</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Money Transfer</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <!-- tab1 -->
                                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                            <div class="card-content">
                                                <div class="card-body">
                                                     <table id="pettysTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>{{ trans('general.title') }}</th>
                                                                <th>Purchase Requisition (PR)</th>
                                                                <th>Date</th>
                                                                <th>Expected Date</th>
                                                                <th>Total</th>
                                                                {{-- <th>Actions</th> --}}
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- tab2 -->
                                        <div class="tab-pane in" id="active2" aria-labelledby="active-tab2" role="tabpane2">

                                            <div class="card-content">
                                                <div class="card-body">
                                                    <table id="billpaymentTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>RMT No.</th>
                                                                <th>Supplier</th>
                                                                <th>Paid From</th>
                                                                <th>Date</th>
                                                                <th>Amount</th>
                                                                <th>Unallocated</th>
                                                                <th>Mode</th>
                                                                <th>Reference</th>
                                                                <th>Bill No</th>                                
                                                                <th>DP No</th>                                
                                                                
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
                                        <div class="tab-pane in" id="active3" aria-labelledby="active-tab3" role="tabpane3">

                                            <div class="card-content">
                                               
                                                <div class="card-body">
                                                     <table id="bankTransTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>#Serial</th>
                                                                <th>Date</th>
                                                                <th>Transfer From</th>
                                                                <th>Transfer Amount</th>
                                                                <th>Transfer To</th>
                                                                <th>Receipt Amount</th>
                                                                <th>Note</th>                                          
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        };
        const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                $('#casual').select2({allowClear:true});
                $('#employee').select2({allowClear:true});
                $('#third_party_user').select2({allowClear:true});
                $('#user_type').change(this.userTypeChange);
                // Index.drawDataTable();
                $('#employee').change(this.employeeChange);
                $('#casual').change(this.employeeChange);
                $('#third_party_user').change(this.thirdPartyChange);
                // $('.transfer_amt').change(this.calcBal)
                
            },
            calcBal(){
                let bill_pay_amt = accounting.unformat($('#amount_total').val());
                let transfer_amt = accounting.unformat($('.transfer_amt').val());
                let balance = transfer_amt - bill_pay_amt;
                console.log(balance, bill_pay_amt,transfer_amt)
                $('.balance').text(accounting.formatNumber(balance));
            },
            employeeChange(){
                if ($.fn.DataTable.isDataTable('#pettysTbl')) {
                    $('#pettysTbl').DataTable().destroy();
                }

                if ($.fn.DataTable.isDataTable('#billpaymentTbl')) {
                    $('#billpaymentTbl').DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable('#bankTransTbl')) {
                    $('#bankTransTbl').DataTable().destroy();
                }

                Index.pmtDataTable();
                Index.moneyTransDataTable();
                setTimeout(() => {
                    Index.calcBal();
                }, 1000);
                return Index.drawDataTable();
            },
            casualChange(){
                if ($.fn.DataTable.isDataTable('#pettysTbl')) {
                    $('#pettysTbl').DataTable().destroy();
                }

                if ($.fn.DataTable.isDataTable('#billpaymentTbl')) {
                    $('#billpaymentTbl').DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable('#bankTransTbl')) {
                    $('#bankTransTbl').DataTable().destroy();
                }
                Index.moneyTransDataTable();
                Index.pmtDataTable();
                setTimeout(() => {
                    Index.calcBal();
                }, 1000);
                return Index.drawDataTable();
            },
            thirdPartyChange(){
                if ($.fn.DataTable.isDataTable('#pettysTbl')) {
                    $('#pettysTbl').DataTable().destroy();
                }

                if ($.fn.DataTable.isDataTable('#billpaymentTbl')) {
                    $('#billpaymentTbl').DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable('#bankTransTbl')) {
                    $('#bankTransTbl').DataTable().destroy();
                }
                Index.moneyTransDataTable();
                Index.pmtDataTable();
                setTimeout(() => {
                    Index.calcBal();
                }, 1000);
                return Index.drawDataTable();
            },
            userTypeChange() {
                let user_type = $('#user_type').val();

                if (user_type === 'employee') {
                    $('.div_employee').removeClass('d-none');
                    $('.div_casual, .div_third_party_user').addClass('d-none');

                    $('#casual').val(null);
                    $('#third_party_user').val(null);

                    $('#casual').select2('destroy').select2();
                    $('#third_party_user').select2('destroy').select2();
                } else if (user_type === 'casual') {
                    $('.div_casual').removeClass('d-none');
                    $('.div_employee, .div_third_party_user').addClass('d-none');

                    $('#employee').val(null);
                    $('#third_party_user').val(null);

                    $('#employee').select2('destroy').select2();
                    $('#third_party_user').select2('destroy').select2();
                } else if (user_type === 'third_party_user') {
                    $('.div_third_party_user').removeClass('d-none');
                    $('.div_employee, .div_casual').addClass('d-none');

                    $('#employee').val(null);
                    $('#casual').val(null);

                    $('#employee').select2('destroy').select2();
                    $('#casual').select2('destroy').select2();
                }
            },

            drawDataTable(params = {}) {
                $('#pettysTbl').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {@lang('datatable.strings')},
                    ajax: {
                        url: '{{ route("biller.petty_cashs.get") }}',
                        type: 'post',
                        data: {
                            user_type: $('#user_type').val(),
                            employee_id: $('#employee').val(),
                            casual_id: $('#casual').val(),
                            third_party_user_id: $('#third_party_user').val(),
                            ...params,
                        },
                        dataSrc: ({data}) => {
                            $('.good-worth').text('0.00');
                            if (data.length && data[0].aggregate) {
                                const aggr = data[0].aggregate;
                                $('.good-worth').text(aggr.good_worth);
                            }
                            return data;
                        },
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {data: 'title', name: 'title'},
                        {data: 'pr_no', name: 'pr_no'},
                        {data: 'date', name: 'date'},
                        {data: 'expected_date', name: 'expected_date'},
                        {data: 'total', name: 'total'},
                    ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            },
            pmtDataTable(params = {}) {
               $('#billpaymentTbl').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    language: {@lang('datatable.strings')},
                    ajax: {
                        url: "{{ route('biller.billpayments.get') }}",
                        type: 'POST',
                        data: {
                            user_type: $('#user_type').val(),
                            employee_id: $('#employee').val(),
                            casual_id: $('#casual').val(),
                            third_party_user_id: $('#third_party_user').val(),
                            ...params,
                        },
                        dataSrc: ({data}) => {
                            $('#amount_total').val(0);
                            $('#unallocated_total').val('');
                            $('.bill_payment_amount').text(0);
                            if (data.length) {
                                const aggregate = data[0].aggregate;
                                $('#amount_total').val(aggregate.amount_total);
                                $('.bill_payment_amount').text(aggregate.amount_total);
                                $('#unallocated_total').val(aggregate.unallocated_total);
                            }
                            return data;
                        },
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {data: 'tid', name: 'tid'},
                        {data: 'supplier', name: 'supplier'},
                        {data: 'account', name: 'account'},                    
                        {data: 'date', name: 'date'},
                        {data: 'amount', name: 'amount'},
                        {data: 'unallocated', name: 'unallocated'},
                        {data: 'payment_mode', name: 'payment_mode'},
                        {data: 'reference', name: 'reference'},
                        {data: 'bill_no', name: 'bill_no'},
                        {data: 'purchase_no', name: 'purchase_no'}
                        // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                    ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            },
            moneyTransDataTable(params = {})
            {
                $('#bankTransTbl').dataTable({
                    stateSave: true,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    language: {@lang('datatable.strings')},
                    ajax: {
                        url: '{{ route("biller.banktransfers.get") }}',
                        type: 'post',
                        data: {
                            user_type: $('#user_type').val(),
                            employee_id: $('#employee').val(),
                            casual_id: $('#casual').val(),
                            third_party_user_id: $('#third_party_user').val(),
                            ...params,
                        },
                        dataSrc: ({data}) => {
                            $('.money_transfer').text('0.00');
                            $('.transfer_amt').val(0);
                            if (data.length && data[0].aggregate) {
                                const aggr = data[0].aggregate;
                                $('.money_transfer').text(aggr.amount);
                                $('.transfer_amt').val(aggr.transfer_amt);
                            }
                            return data;
                        },
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        ...['tid', 'transaction_date', 'source_account',  'credit', 'recepient_account', 'debit'].map(v => ({data:v, name:v})),
                        {
                            data: 'note',
                            name: 'note',
                            searchable: false,
                            sortable: false
                        }
                    ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            }
        };
        $(()=>Index.init());
    </script>
@endsection
