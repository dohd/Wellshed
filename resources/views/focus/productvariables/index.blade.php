@extends ('core.layouts.app')

@section ('title', trans('labels.backend.productvariables.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.productvariables.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.productvariables.partials.productvariables-header-buttons')
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
                            <table id="productvariables-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>UoM Title</th>
                                        <th>UoM Code</th>
                                        <th>Unit Type</th>
                                        <th>Compound Ratio (PBU)</th>
                                        <th>Counted As</th>                                       
                                        <th>Products Count</th>                                       
                                        <th>Actions</th>
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajaxSetup);
            this.drawDataTable();
        },

        drawDataTable() {
            $('#productvariables-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: { @lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.productvariables.get") }}',
                    type: 'post'
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'title', name: 'title'},
                    {data: 'code', name: 'code'},
                    {data: 'unit_type', name: 'unit_type'},
                    {data: 'base_ratio', name: 'base_ratio'},
                    {data: 'count_type', name: 'count_type'},
                    {data: 'total', name: 'total'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
        }
    }

    $(() => Index.init());
</script>
@endsection
