@extends ('core.layouts.app')

@section ('title', 'Delivery Management')

@section('page-header')
    <h1>Delivery Management</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Delivery Management</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.deliveries.partials.deliveries-header-buttons')
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
                                    <table id="deliveries-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Delivery No.</th>
                                            <th>Order</th>
                                            <th>Customer</th>
                                            <th>Delivery Schedule No.</th>
                                            <th>Delivery Date</th>
                                            {{-- <th>Status</th> --}}
                                            <th>{{ trans('labels.general.actions') }}</th>
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
    </div>
@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        $(function () {
            setTimeout(function () {
                draw_data()
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#deliveries-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.deliveries.get") }}',
                    type: 'post'
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'tid', name: 'tid'},
                    {data: 'order', name: 'order'},
                    {data: 'customer', name: 'customer'},
                    {data: 'delivery_schedule', name: 'delivery_schedule'},
                    {data: 'date', name: 'date'},
                    // {data: 'status', name: 'status'},
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
            $('#deliveries-table_wrapper').removeClass('form-inline');

        }
    </script>
@endsection
