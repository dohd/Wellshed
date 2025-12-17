@extends ('core.layouts.app')
@section ('title', 'Whatsapp Messages Status Report')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Whatsapp Messages Status Report</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.whatsapp.partials.message-header-buttons')
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
                                <table id="statusesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Phone No.</th>
                                            <th>Time</th>
                                            <th>Status</th>
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
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Authorization': "Bearer {{ config('agentToken') }}",
            } 
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };
    
    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);

            Index.drawData();
        },

        drawData() {
            $('#statusesTbl').dataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                ajax: {
                    url: '{{ route("biller.whatsapp_statuses.get") }}',
                    type: 'POST',
                    data: {
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                    },
                },
                columns: [
                    {data: 'DT_Row_Index',name: 'id'},
                    ...['recipient_id', 'timestamp', 'status'].map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };
    
    $(Index.init);
</script>
@endsection
