@extends ('core.layouts.app')

@section ('title', 'RFQ Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">RFQ Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.rfq.partials.rfq-header-buttons')
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
                            <table id="purchaseordersTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>RFQ No</th>
                                        {{-- <th>{{ trans('suppliers.supplier') }}</th> --}}
                                        <th>Subject</th>
                                        <th>PR No</th>
                                        <th>Project</th>
                                        <th>Status</th>
                                        {{-- <th>Product Types</th> --}}
                                        {{-- <th>{{ trans('general.amount') }}</th> --}}
                                        <th>RFQ Date</th>
                                        <th>Due Date</th>
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
                    url: '{{ route("biller.rfq.get") }}',
                    type: 'post',
                    data: {
                        supplier_id: $('#supplier').val(),
                        status: $('#status').val(),
                    },
                    dataSrc: ({data}) => {
                        $('#order_total').val('');
                        $('#grn_total').val('');
                        $('#due_total').val('');
                        // if (data.length && data[0].aggregate) {
                        //     const aggr = data[0].aggregate;
                        //     $('#order_total').val(aggr.order_total);
                        //     $('#grn_total').val(aggr.grn_total);
                        //     $('#due_total').val(aggr.due_total);
                        // }
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
                    // {
                    //     data: 'supplier',
                    //     name: 'supplier'
                    // },
                    {
                        data: 'subject',
                        name: 'subject'
                    },
                    {
                        data: 'pr_no',
                        name: 'pr_no'
                    },
                    {
                        data: 'project_no',
                        name: 'project_no'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    // {
                    //     data: 'amount',
                    //     name: 'amount'
                    // },
                    {
                        data: 'rfq_date',
                        name: 'rfq_date'
                    },
                    {
                        data: 'due_date',
                        name: 'due_date'
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