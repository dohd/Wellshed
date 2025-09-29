@extends ('core.layouts.app')

@section ('title', 'Manage Sale Agents')

@section('page-header')
    <h1>{{ 'Manage Sale Agents' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Manage Sale Agents' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.sale_agents.partials.sale_agents-header-buttons')
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
                                    <table id="sale_agents-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Phone Number</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>City / Town</th>
                                            <th>Invited By</th>
                                            <th>{{ trans('general.createdat') }}</th>
                                            {{-- <th>{{ trans('labels.general.actions') }}</th> --}}
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
    {{ Html::script('focus/js/select2.min.js') }}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>

        const config = {
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
            datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
        };
        const Index = {
            start_date: '',
            end_date: '',
            
            init(){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
                
                Index.drawDataTable();
            },
            drawDataTable() {
                $('#sale_agents-table').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {@lang('datatable.strings')},
                    ajax: {
                        url: '{{ route("biller.sale_agents.get") }}',
                        type: 'post',
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {data: 'name', name: 'name'},
                        {data: 'phone', name: 'phone'},
                        {data: 'email', name: 'email'},
                        {data: 'county', name: 'county'},
                        {data: 'city', name: 'city'},
                        {data: 'referral_code', name: 'referral_code'},
                        {data: 'created_at', name: 'created_at'}
                        // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                    ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            },
        };
        $(Index.init)
    </script>
@endsection
