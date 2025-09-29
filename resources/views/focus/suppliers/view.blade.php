@extends('core.layouts.app', [
    'page' => 'class = "horizontal-layout horizontal-menu content-detached-left-sidebar app-contacts" data-open = "click" data-menu = "horizontal-menu" data-col = "content-detached-left-sidebar"'
])

@section('title', trans('labels.backend.suppliers.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Supplier Management {{$supplier->id}}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.suppliers.partials.suppliers-header-buttons')
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

                                <div class="btn-group float-right">
                                    @permission('edit-supplier')
                                        @if(empty(Auth::user()->supplier_id))
                                            <a href="{{ route('biller.suppliers.edit', $supplier) }}" class="btn btn-blue btn-outline-accent-5 btn-sm">
                                                <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                            </a>&nbsp;
                                        @endif
                                    @endauth

                                    @permission('delete-supplier')
                                        <button type="button" class="btn btn-danger btn-outline-accent-5 btn-sm" id="delSupplier">
                                            {{Form::open(['route' => ['biller.suppliers.destroy', $supplier], 'method' => 'DELETE'])}}{{Form::close()}}
                                            <i class="fa fa-trash"></i> {{trans('buttons.general.crud.delete')}}
                                        </button>
                                    @endauth
                                </div>

                                <div class="card-body">
                                    @include('focus.suppliers.partials.tabs')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @include('focus.suppliers.partials.sidebar')
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
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
        },
        poSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.suppliers.purchaseorders_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({term, supplier_id: "{{ $supplier->id }}"}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.tid + ' - ' + v.note, id: v.id})) }
                },
            }
        },
    };

    const View = {
        startDate: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#poSelect').select2(config.poSelect2);
            $('#poSelect2').select2(config.poSelect2);
            
            View.drawSupplierDataTable();
            View.drawBillDataTable();
            View.drawAccountStatementDataTable();
            View.drawBillStatementDataTable();
            View.cloneAgingReport();
            View.drawGrnItemsBySupplierTable()

            $('#poSelect').change(() => $('#refresh2').click());
            $('#poSelect2').change(() => $('#refresh4').click());

            $('.start_date').change(View.changeStartDate);
            $('.search').click(View.searchClick);
            $('.refresh').click(View.refreshClick);
            $('#delSupplier').click(View.deleteSupplier);
        },

        deleteSupplier() {
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
                View.drawBillStatementDataTable();
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
                View.drawBillStatementDataTable();
            }
            else if (id == 'refresh5') {
                $('#grn-table').DataTable().destroy();
                View.drawGrnItemsBySupplierTable();
            }
        },

        cloneAgingReport() {
            $('.stment-aging-wrapper').append($('.aging').clone());
        },

        drawSupplierDataTable() {
            $('#supplierTbl').DataTable({
                ...config.dataTable,
                ajax: {
                    url: '{{ route("biller.suppliers.get") }}',
                    type: 'post',
                    data: {supplier_id: "{{ $supplier->id }}" },
                },
                columns: [{ data: 'name', name: 'name'}],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'frt',
            });
        },

        // Bills
        drawBillDataTable() {
            $('#billTbl').DataTable({
                ...config.dataTable,
                ajax: {
                    url: '{{ route("biller.suppliers.get") }}',
                    type: 'post',
                    data: {supplier_id: "{{ $supplier->id }}", start_date:View.startDate, is_bill: 1 },
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

        // Statement on Account
        drawAccountStatementDataTable() {
            $('#transTbl').DataTable({
                ...config.dataTable,
                ajax: {
                    url: '{{ route("biller.suppliers.get") }}',
                    type: 'post',
                    data: {
                        is_transaction: 1,
                        supplier_id: "{{ $supplier->id }}", 
                        start_date: View.startDate, 
                        purchaseorder_id: $('#poSelect').val(),
                    },
                },
                columns: [
                    {name: 'id', data: 'DT_Row_Index'},
                    ...['date', 'type', 'note', 'bill_amount', 'amount_paid', 'account_balance'].map(v => ({data: v, name: v})),
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

        // Statement on Bill
        drawBillStatementDataTable() {
            $('#stmentTbl').DataTable({
                ...config.dataTable,
                bSort: false,
                ajax: {
                    url: '{{ route("biller.suppliers.get") }}',
                    type: 'post',
                    data: {
                        is_statement: 1,
                        supplier_id: "{{ $supplier->id }}", 
                        start_date: View.startDate, 
                        purchaseorder_id: $('#poSelect2').val(),
                    },
                },
                columns: [
                    {name: 'id', data: 'DT_Row_Index'},
                    ...['date', 'type', 'note', 'bill_amount', 'amount_paid', 'bill_balance'].map(v => ({data: v, name: v})),
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

        drawGrnItemsBySupplierTable() {
            var dataTable = $('#grn-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("biller.grn-items-by-supplier-v2") }}',
                    type: 'GET',
                    data: {
                        supplierId: "{{ $supplier->id }}",
                    },
                },
                columns: [
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'uom',
                        name: 'uom'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    {
                        data: 'value',
                        name: 'value'
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     searchable: false,
                    //     sortable: false
                    // }
                ],
                order: [
                    [1, "desc"]
                ],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(View.init);
</script>
@endsection