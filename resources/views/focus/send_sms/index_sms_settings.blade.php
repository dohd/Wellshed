@extends ('core.layouts.app')

@section ('title', 'Manage SMS Settings')

@section('page-header')
    <h1>{{ 'Manage  SMS Settings' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Manage  SMS Settings' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.send_sms.partials.sms_setting-header-buttons')
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
                                    <table id="send_sms-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>SenderID</th>
                                            <th>Status</th>
                                            <th>Tenant</th>
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

                                @include('focus.send_sms.partials.sms_settings_modal')
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

        const config = {};
        const Index = {
            
            init(){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $('#send_sms-table tbody').on('click', 'a.view_task', function (e) {
                    e.preventDefault(); // Prevent default anchor behavior if needed
                    const el = $(this); // 'this' refers to the clicked 'a' element
                    const row = el.closest('tr'); // Find the closest 'tr' element
                    const dataId = el.data('id'); // Get the 'data-id' attribute
                    $('#setting_id').val(dataId); // Set the value if needed
                });
                Index.drawDataTable();
            },
            drawDataTable() {
                $('#send_sms-table').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {@lang('datatable.strings')},
                    ajax: {
                        url: '{{ route("biller.send_sms.get_sms_settings") }}',
                        type: 'post'
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {data: 'driver', name: 'driver'},
                        {data: 'sender', name: 'sender'},
                        {data: 'status', name: 'status'},
                        {data: 'tenant', name: 'tenant'},
                        {data: 'actions', name: 'actions', searchable: false, sortable: false}
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
