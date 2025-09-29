@extends ('core.layouts.app')
@section ('title', 'Reclassify Transactions')

@section('content')
<div class="content-wrapper">
    <div class="content-body"> 
        <div class="row match-height">
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-bottom-blue-grey">
                        <div class="row no-gutters">
                            <div class="col-11">
                                <h4 class="card-title">Reclassify Transactions</h4>
                            </div>
                            <div class="col-1">
                                <a href="#" class="btn btn-success reclassify-btn" data-toggle="modal" data-target="#reclassifyModal_">
                                    <i class="fa fa-pencil" aria-hidden="true"></i> <b>Reclassify</b>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <!-- Accounts -->
                                <div class="col-3 border-right border-secondary border-light" style="border-width:.3em!important;">
                                    <div class="row no-gutters mb-1">
                                        <div class="col-6">
                                            <select id="account-category" class="custom-select">
                                                <option value="profit_and_loss">Profit and Loss</option>
                                                <option value="balance_sheet">Balance Sheet</option>
                                            </select>
                                        </div>
                                    </div>
                                    <table id="accounts-tbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($accounts as $row)
                                                <tr>
                                                    <td><a href="#" class="row-name" accountDetail="{{ @$row->account_type_detail->system }}">{{ $row->holder }}</a><br><span class="text-secondary">{{ $row->account_type }}</span></td>
                                                    <td>{{ numberFormat($row->balance) }}</td>
                                                    <input type="hidden" class="row-category" value="{{ $row->category }}">
                                                    <input type="hidden" class="row-id" value="{{ $row->id }}">
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Transactions -->
                                <div class="col-9">
                                    <div class="row mb-2">
                                        <div class="col-3">
                                            <select id="customer" name="customer" class="custom-select" data-placeholder="Choose Customer">
                                                <option value=""></option>
                                                @foreach ($customers as $row)
                                                    <option value="{{ $row->id }}">{{ $row->company ?: $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <select id="supplier" class="custom-select" data-placeholder="Choose Vendor">
                                                <option value=""></option>
                                                @foreach ($suppliers as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name ?: $row->company }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <select id="project" class="custom-select" data-placeholder="Choose Project">
                                                <option value=""></option>
                                                @foreach ($projects as $row)
                                                    <option value="{{ $row->id }}">
                                                        {{ gen4tid('PRJ-', $row->tid) }} || 
                                                        {{ @$row->custome->company ?: @$row->customer->name }} || 
                                                        {{ substr($row->name,0,20).'...' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-2">
                                            <select id="classlist" class="custom-select" data-placeholder="Choose Class/Subclass">
                                                <option value=""></option>
                                                @foreach ($classlists as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-md-2">Search Date Between</div>
                                        <div class="col-md-2">
                                            <input type="text" name="start_date" id="start_date" class="date30 form-control form-control-sm datepicker">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                        </div>
                                    </div>
                                    <div class="row ">
                                        <div class="col-12">
                                            <b>Account:</b> <span class="text-secondary" id="account-name"></span>
                                        </div>
                                    </div>
                                    <hr>
                                    <table id="transactions-tbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="check-all"></th> 
                                                <th class="th-date">Date</th>
                                                <th>Type</th>
                                                <th>Tr No.</th>
                                                <th>Account</th>   
                                                <th>Payer</th>
                                                <th>Payee</th>
                                                <th>Project</th>                                                 
                                                <th>Note</th>
                                                <th>Debit</th>
                                                <th>Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
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
@include('focus.general.settings.reclassify-modal')
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script>
    const config = {
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        date: {format: "{{config('core.user_date_format')}}", autoHide: true}, 
    };

    const Form = {
        initAccountRows: '',
        startDate: '',
        endDate: '',
        accountDetail: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#supplier').select2({allowClear: true});
            $('#classlist').select2({allowClear: true});
            $('#project').select2({allowClear: true});

            Form.initAccountRows = $('#accounts-tbl tbody tr');
            // modal config
            $('#reclassifyModal').on('shown.bs.modal', function () {
                $('#dest-account').select2({allowClear: true, dropdownParent: $(this)});
                $('#dest-class').select2({allowClear: true, dropdownParent: $(this)});
            });

            $('#search').click(Form.clickSearch);
            $('#check-all').click(Form.checkAllRows);
            $('#account-category').change(Form.changeAccountType).change();
            $(document).on('change', '.check-row', Form.checkRow);
            $(document).on('click', '.row-name', Form.clickAccount);
            $(document).on('click', '.reclassify-btn', Form.clickReclassifyBtn);
            $(document).on('change', '#customer, #supplier, #classlist, #project', Form.changeTrFilters);
        },

        clickReclassifyBtn(e) {
            if (!$('#tr-id').val()) {
                return swal({
                    title: 'Select transactions to proceed!',
                    icon: "error",
                    dangerMode: true,
                });
            }
            swal({
                title: 'Are You  Sure?',
                text: "Once applied, you will not be able to undo!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((isOk) => {
                if (isOk) $('#reclassifyModal').modal('show');
            }); 
        },

        changeTrFilters() {
            // reload transactions
            $('#transactions-tbl').DataTable().destroy();
            Form.drawTransactionsDT();
        },

        checkAllRows() {
            if ($(this).prop('checked')) {
                $('.check-row').prop('checked', true);
                let accountIds = [];
                $('.check-row').each(function() {
                    accountIds.push($(this).attr('data-id'));
                });
                $('#tr-id').val(accountIds.join(','));
            } else {
                $('.check-row').prop('checked', false);
                $('#tr-id').val('');
            }
        },

        checkRow() {
            const id = $(this).attr('data-id');
            const accountIds = $('#tr-id').val()? $('#tr-id').val().split(',') : [];
            if ($(this).prop('checked')) {
                accountIds.push(id);
            } else {
                accountIds.splice(accountIds.indexOf(id), 1);
            }
            $('#tr-id').val(accountIds.join(','));
        },

        clickSearch() {
            Form.startDate = $('#start_date').val();
            Form.endDate =  $('#end_date').val();
            if (!Form.startDate && !Form.endDate ) 
                return alert("Date range required!");
            // reload transactions
            $('#transactions-tbl').DataTable().destroy();
            Form.drawTransactionsDT();
        },

        clickAccount() {
            const row = $(this).parents('tr');
            $('#account-name').html($(this).html());
            $('#prev-account').val(row.find('.row-id').val());
            Form.accountDetail = $(this).attr('accountDetail');
            // reload transactions
            $('#transactions-tbl').DataTable().destroy();
            Form.drawTransactionsDT();
        },

        // Profit and Loss / Balance Sheet
        changeAccountType() {
            $('#accounts-tbl').DataTable().destroy();  
            $('#accounts-tbl tbody').html(Form.initAccountRows);
            const accountCategory = $(this).val();
            $('.row-category').each(function() {
                if ($(this).val() != accountCategory) {
                    $(this).parents('tr').remove();
                } 
            });   
            $('#accounts-tbl').DataTable({dom: 'frtip'});
        },

        drawTransactionsDT() {
            $('#transactions-tbl').DataTable({
                processing: true,
                // serverSide: true,
                responsive: true,
                stateSave: true,
                ajax: {
                    url: '{{ route("biller.transactions.get") }}',
                    type: 'post',
                    data: {
                        start_date: Form.startDate, 
                        end_date: Form.endDate,
                        rel_type: 9, // account transaction type
                        rel_id: $('#prev-account').val(), // account id
                        exempt_cols: ['man_journal_id'], // journal entries
                        customer_id: $('#customer').val(),
                        supplier_id: $('#supplier').val(),
                        classlist_id: $('#classlist').val(),
                        project_id: $('#project').val(),
                        system: Form.accountDetail == 'work_in_progress'? 'wip' : '',
                    },
                    // dataSrc: ({data}) => {
                    //     if (data.length && $('#project').val()) {
                    //         const filteredData = data.filter(v => v.project_id == $('#project').val());
                    //         return filteredData;
                    //     }                        
                    //     return data;
                    // },
                },
                columns: [
                    {data: 'row_check', name: 'row_check', sortable: false, searchable: false},
                    ...[
                        'tr_date',
                        'tr_type', 
                        'tid', 
                        'reference', 
                        'payer',
                        'payee',
                        'project',
                        'note', 
                        'debit', 
                        'credit',
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Form.init);
</script>
@endsection
