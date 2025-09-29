@extends ('core.layouts.app')

@section ('title', 'Customer Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Customer Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.customers.partials.customers-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-detached content-right">
        <div class="content-body">
            <section class="row all-contacts">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                @if (access()->allowMultiple(['edit-client', 'delete-client']))
                                    <div class="btn-group float-right">
                                        @permission('edit-client')
                                            <a href="{{ route('biller.customers.edit', $customer) }}" class="btn btn-blue btn-outline-accent-5 btn-sm">
                                                <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                            </a>&nbsp;
                                        @endauth
                                        @permission('delete-client')
                                            <button type="button" class="btn btn-danger btn-outline-accent-5 btn-sm" id="delCustomer">
                                                {{ Form::open(['route' => ['biller.customers.destroy', $customer], 'method' => 'DELETE']) }}{{ Form::close() }}
                                                <i class="fa fa-trash"></i> {{ trans('buttons.general.crud.delete') }}
                                            </button>
                                        @endauth
                                    </div>
                                @endif
                                <div class="card-body">
                                    @include('focus.customers.partials.tabs')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('focus.customers.partials.sidebar')
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        dataTable: {
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
        }
    };

    const View = {
        startDate: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#project').select2({allowClear: true});
            $('#project2').select2({allowClear: true});
            
            View.drawCustomerDataTable();
            View.drawInvoiceDataTable();
            View.drawAccountStatementDataTable();
            View.drawInvoiceStatementDataTable();
            View.cloneAgingReport();

            $('.start_date').change(View.changeStartDate);
            $('.search').click(View.searchClick);
            $('.refresh').click(View.refreshClick);
            $('#delCustomer').click(View.deleteCustomer);

            // paid project filter on account statement
            $('#project').change(function() {
                $('#transTbl').DataTable().destroy();
                View.drawAccountStatementDataTable();
            });
            // paid project filter on invoice statement
            $('#project2').change(function() {
                $('#stmentTbl').DataTable().destroy();
                View.drawInvoiceStatementDataTable();
            });
        },

        deleteCustomer() {
            const form = $(this).children('form');
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => form.submit());
        },

        changeStartDate() {
            const date = $(this).val();
            // statement on account
            if ($(this).parents('#active2').length) {
                let link = $('.print-on-account').attr('href');
                if (link.includes('start_date')) {
                    link = link.split('?')[0];
                    link += `?is_transaction=1&start_date=${date}`;
                } else link += `?is_transaction=1&start_date=${date}`;
                $('.print-on-account').attr('href', link);
            } else if ($(this).parents('#active4').length) {
                // statement on invoice
                let link = $('.print-on-invoice').attr('href');
                if (link.includes('start_date')) {
                    link = link.split('?')[0];
                    link += `?is_statement=1&start_date=${date}`;
                } else link += `?is_statement&start_date=${date}`;
                $('.print-on-invoice').attr('href', link);
            }
        },

        searchClick() {
            const startInpt = $(this).parents('.row').find('.start_date');
            const id = $(this).attr('id');
            if (id == 'search2') {
                View.startDate = startInpt.eq(0).val();
                $('#transTbl').DataTable().destroy();
                View.drawAccountStatementDataTable();
            } else if (id == 'search4') {
                View.startDate = startInpt.eq(1).val();
                $('#stmentTbl').DataTable().destroy();
                View.drawInvoiceStatementDataTable();
            }
        },

        refreshClick() {
            View.startDate = '';
            View.endDate = '';
            const id = $(this).attr('id');
            if (id == 'refresh2') {
                $('#transTbl').DataTable().destroy();
                View.drawAccountStatementDataTable();
            } else if (id == 'refresh4') {
                $('#stmentTbl').DataTable().destroy();
                View.drawInvoiceStatementDataTable();
            }
        },

        cloneAgingReport() {
            const aging = $('.aging').clone();
            $('#stmentTbl').after(aging);
            $('#active5').append(aging.clone());
        },

        drawCustomerDataTable() {
            $('#customerTbl').DataTable({
                ...config.dataTable,
                ajax: {
                    url: '{{ route("biller.customers.get") }}',
                    type: 'post',
                    data: { customer_id: "{{ $customer->id }}" },
                },
                columns: [{ data: 'company', name: 'company' }],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'lfrtip', // l adds the length menu
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]], // Add rows selection and "All" option
            });
        },

        drawInvoiceDataTable() {
            $('#invoiceTbl').DataTable({
                ...config.dataTable,
                ajax: {
                    url: '{{ route("biller.customers.get") }}',
                    type: 'post',
                    data: {customer_id: "{{ $customer->id }}", start_date:this.startDate, is_invoice: 1 },
                },
                columns: [
                    {name: 'id', data: 'DT_Row_Index'},
                    ...['date', 'status', 'note', 'amount', 'paid'].map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['excel', 'csv', 'pdf'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        },

        drawAccountStatementDataTable() {
            $('#transTbl').DataTable({
                ...config.dataTable,
                ajax: {
                    url: '{{ route("biller.customers.get") }}',
                    type: 'post',
                    data: {
                        customer_id: "{{ $customer->id }}", 
                        project_id: $('#project').val(),
                        start_date: this.startDate, 
                        is_transaction: 1 
                    },
                },
                columns: [
                    {name: 'id', data: 'DT_Row_Index'},
                    ...['date', 'type', 'note', 'invoice_amount', 'amount_paid', 'account_balance'].map(v => ({data: v, name: v})),
                ],
                order: [[1, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['excel', 'csv', 'pdf'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        },

        drawInvoiceStatementDataTable() {
            $('#stmentTbl').DataTable({
                ...config.dataTable,
                bSort: false,
                ajax: {
                    url: '{{ route("biller.customers.get") }}',
                    type: 'post',
                    data: {
                        customer_id: "{{ $customer->id }}", 
                        start_date: this.startDate, 
                        project2_id: $('#project2').val(),
                        is_statement: 1,
                    },
                },
                columns: [
                    {name: 'id', data: 'DT_Row_Index'},
                    ...['date', 'type', 'note', 'invoice_amount', 'amount_paid', 'invoice_balance'].map(v => ({data: v, name: v})),
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['excel', 'csv', 'pdf'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        },
    };

    $(() => View.init());
</script>
@endsection