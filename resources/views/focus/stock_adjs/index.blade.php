@extends ('core.layouts.app')
@section('title', 'Stock Adjustment')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Adjustment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_adjs.partials.stockadjs-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table id="stockadjsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Ledger Account</th>
                                <th>Total</th> 
                                <th>Adj. Type</th>
                                <th>Note</th>                                                     
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
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            Index.drawDataTable();
        },

        drawDataTable() {
            $('#stockadjsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.stock_adjs.get') }}",
                    type: 'POST',
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...['approval_status', 'date', 'account', 'total', 'adj_type', 'note', ].map(v => ({data: v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(Index.init);
</script>
@endsection
