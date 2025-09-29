@extends ('core.layouts.app')

@section ('title', trans('labels.backend.purchaseorders.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.purchaseorders.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row form-group">
                            <div class="col-md-4 col-12">
                                <label for="customer">Supplier</label>
                                <select name="supplier_id" id="supplier" class="form-control" data-placeholder="Choose Supplier">
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-12">
                                <label for="status">Order Status</label>
                                <select name="status" id="status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['Pending', 'Partial', 'Complete', 'Closed'] as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 col-12">
                                <label for="amount">Total Order Amount</label>
                                <input type="text" id="order_total" class="form-control" readonly>
                            </div>    
                            <div class="col-md-2 col-12">
                                <label for="amount">Total Received Amount</label>
                                <input type="text" id="grn_total" class="form-control" readonly>
                            </div>     
                            <div class="col-md-2 col-12">
                                <label for="amount">Total Pending Amount</label>
                                <input type="text" id="due_total" class="form-control" readonly>
                            </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="purchaseordersTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>#Order No</th>
                                            <th>Date</th>
                                            <th>{{ trans('suppliers.supplier') }}</th>
                                            <th>Note</th>
                                            <th>{{ trans('general.amount') }}</th>
                                            <th>Approval Status</th>
                                            <th>Delivery {{ trans('general.status') }}</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
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
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {
            headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
        },
    };
    
    const Index = {
        init() {
            $.ajaxSetup(config.ajax);

            $('#status').change(this.statusChange);
            $('#supplier').select2({allowClear: true}).val('').trigger('change')
            .change(this.supplierChange);

            this.drawDataTable();
        },

        supplierChange() {
            $('#purchaseordersTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        statusChange() {
            $('#purchaseordersTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#purchaseordersTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.purchaseorders.get") }}',
                    type: 'post',
                    data: {
                        supplier_id: $('#supplier').val(),
                        status: $('#status').val(),
                    },
                    dataSrc: ({data}) => {
                        $('#order_total').val('');
                        $('#grn_total').val('');
                        $('#due_total').val('');
                        if (data.length && data[0].aggregate) {
                            const aggr = data[0].aggregate;
                            $('#order_total').val(aggr.order_total);
                            $('#grn_total').val(aggr.grn_total);
                            $('#due_total').val(aggr.due_total);
                        }
                        return data;
                    },
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },                    
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'supplier',
                        name: 'supplier'
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },                    
                    {
                        data: 'approval_status',
                        name: 'approval_status'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(() => Index.init());
</script>
@endsection