@extends ('core.layouts.app')

@section ('title', trans('labels.backend.additionals.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>{{ trans('labels.backend.additionals.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.additionals.partials.additionals-header-buttons')
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
                            <div class="alert alert-info"> 
                                {{trans('business.manage_default_tax_discount')}}
                                <a class="btn btn-purple" href="{{route('biller.settings.billing_preference')}}">
                                    <i class="fa fa-files-o"></i> {{trans('business.billing_settings_preference')}}
                                </a> .
                            </div>
                            <table id="additionals-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Rate</th>
                                        <th>Default</th>
                                        <th>{{ trans('general.createdat') }}</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
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
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    function draw_data() {
        const language = {@lang('datatable.strings')};
        const dataTable = $('#additionals-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language,
            ajax: {
                url: "{{ route('biller.additionals.get') }}",
                type: 'POST'
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'value', name: 'value'},
                {data: 'is_default', name: 'is_default'},
                {data: 'created_at', name: 'created_at'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [
                    {extend: 'csv', footer: true, exportOptions: {columns: [0, 1]}},
                    {extend: 'excel', footer: true, exportOptions: {columns: [0, 1]}},
                    {extend: 'print', footer: true, exportOptions: {columns: [0, 1]}}
                ]
            }
        });
    }
</script>
@endsection