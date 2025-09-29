@extends ('core.layouts.app')

@section ('title', 'Selling Price Variation')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title mb-0">Selling Price Variation</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.spvariations.partials.spvariations-header-buttons')
                </div>
            </div>
        </div>
    </div>
    @if ($segment)
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-2">
                        <p> 
                            @if (request('rel_type') == 2) 
                                {{trans('warehouses.title')}}@else {{trans('productcategories.title')}} 
                            @endif
                         </p>
                    </div>
                    <div class="col-sm-6">
                        <p>{{$segment['title']}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2">
                        <p>{{trans('productcategories.extra')}}</p>
                    </div>
                    <div class="col-sm-6">
                        <p>{{$segment['extra']}}</p>
                    </div>
                </div> 
                @if (!$segment['c_type'])
                    <div class="row">
                        <div class="col-sm-2">
                            <p>{{trans('productcategories.total_products')}}</p>
                        </div>
                        <div class="col-sm-6">
                            <p>33</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2">
                            <p>{{trans('productcategories.total_worth')}}</p>
                        </div>
                        <div class="col-sm-6">
                            <p>33</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(isset($segment->subcategories[0]))
        <div class="card p-1 bg-lighten-5">
            <h4 class="mb-0">{{ trans('productcategories.sub_categories') }}</h4>
            <table id="productcategories-table"
                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                    width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('productcategories.title') }}</th>
                    <th>{{ trans('general.createdat') }}</th>
                    <th>{{ trans('labels.general.actions') }}</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endif

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="spvariations-table"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('labels.backend.products.table.id') }}</th>
                                        <th>Item Name</th>
                                        <th>Item Code</th>
                                        <th>Price</th>
                                        <th>Date Created</th>                                  
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i
                                                    class="fa fa-spinner spinner"></i></td>
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
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $(function () {
        setTimeout(function () {
            draw_data();
        }, "{{ config('master.delay') }}");
    });

    function draw_data() {
        const language = {@lang('datatable.strings')}
        var dataTable = $('#spvariations-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language,
            ajax: {
                url: '{{ route("biller.spvariations.get") }}',
                type: 'post',
                @if($segment) data: {p_rel_id: '{{@$segment['id']}}', p_rel_type: '{{@$input['rel_type']}}'},@endif },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'item_name', name: 'item_name'},
                {data: 'item_code', name: 'item_code'},
                {data: 'selling_price', name: 'selling_price'},
                {data: 'created_at', name: '{{config('module.products.table')}}.created_at'},
                
            ],
            order: [[0, "asc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: {
                buttons: [

                    {extend: 'csv', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6]}},
                    {extend: 'excel', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6]}},
                    {extend: 'print', footer: true, exportOptions: {columns: [0, 1, 2, 3, 4, 5, 6]}}
                ]
            },
        });
    }

    function sub_draw_data() {
        const language = {@lang('datatable.strings')}
        var dataTable = $('#spvariations-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language,
            ajax: {
                url: '{{ route("biller.spvariations.get") }}',
                type: 'post',
                data: {rel_type: '1', rel_id:'{{request('rel_id',0)}}'}
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'created_at', name: '{{config('module.productcategories.table')}}.created_at'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "asc"]],
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