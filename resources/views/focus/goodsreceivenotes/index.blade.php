@extends ('core.layouts.app')

@section('title', 'Goods Receive Note')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Goods Receive Note</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.goodsreceivenotes.partials.goodsreceivenotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <label for="customer">Supplier</label>
                                <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose Supplier">
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }} {{ $supplier->goods_receive_notes->count() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="inv_status">Goods Reception Status</label>
                                <select name="inv_status" id="inv_status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['with_invoice', 'without_invoice'] as $status)
                                        <option value="{{ $status }}">{{  ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <div class="mb-2">Total Cost</div>                           
                                <div class="good-worth">0.00</div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-2">{{ trans('general.search_date')}}</div>
                            @php
                                $now = date('d-m-Y');
                                $start = date('d-m-Y', strtotime("{$now} - 1 months"));
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
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="grnTbl" class="table table-striped table-bordered zero-configuration" width="100%" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>GRN No.</th>
                                        <th>Supplier</th>
                                        <th>Purchase Type</th>
                                        <th>CU Invoice No</th>
                                        <th>Dnote</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Note</th>                                        
                                        <th>Action</th>
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        start_date: '',
        end_date: '',

        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('.datepicker').change(this.dateChange);
            $('#inv_status').change(this.invoiceStatusChange);
            $('#supplier').select2({allowClear: true}).val('').trigger('change')
            .change(this.supplierChange);
            $('#search').click(this.filterCriteriaChange);

            this.drawDataTable();
        },

        filterCriteriaChange() {

            $('#grnTbl').DataTable().destroy();
            return Index.drawDataTable({
            });   
        },

        invoiceStatusChange() {
            $('#grnTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        supplierChange() {
            $('#grnTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        dateChange() {
            let start = $('#start_date').val();
            let end = $('#end_date').val();
            if (start && end) {
                Index.start_date = start;
                Index.end_date = end;
            } else {
                Index.start_date = '';
                Index.end_date = '';
            }
        },

        drawDataTable(params={}) {
            $('#grnTbl').dataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.goodsreceivenote.get') }}",
                    type: 'POST',
                    data: {
                        invoice_status: $('#inv_status').val(),
                        supplier_id: $('#supplier').val(),
                        start_date: this.start_date, 
                        end_date: this.end_date,
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
                    {data: 'tid', name: 'tid'},
                    {data: 'supplier', name: 'supplier'},
                    {data: 'purchase_type', name: 'purchase_type'},
                    {data: 'invoice_no', name: 'invoice_no'},
                    {data: 'dnote', name: 'dnote'},
                    {data: 'date', name: 'date'},
                    {data: 'total', name: 'total'},
                    {data: 'note', name: 'note'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [10, 25, 50, 100, 200, -1],
                    [10, 25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });
        }
    };

    $(() => Index.init(config));
</script>
@endsection
