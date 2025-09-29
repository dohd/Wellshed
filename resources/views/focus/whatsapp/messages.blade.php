@extends ('core.layouts.app')
@section ('title', 'Whatsapp Messages Report')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Whatsapp Messages Report</h4>
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
                                <table id="whatsappBroadcastTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Phone</th>
                                            <th>Message Template</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Fail Reason</th>
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
            // Fetch Broadcast Report
            $.post("{{ route('api.whatsapp_broadcast.report') }}", {
                'start_date': '2020-01-01',
                'end_date': "{{ date('Y-m-d') }}"
            })
            .then(({messages}) => {
                $('#whatsappBroadcastTbl tbody tr').remove();
                if (messages && messages.length) {
                    messages.forEach((v,i) => {
                        let code = 1, status = '';
                        if (v.sent) status = 'Sent';
                        if (v.delivered) status = 'Delivered';
                        if (v.read) status = 'Read';
                        if (v.failed) {
                            code = 0;
                            status = 'Failed';
                        }

                        $('#whatsappBroadcastTbl tbody').append(`
                            <tr>
                                <td>${i+1}</td>
                                <td>${v.number}</td>
                                <td>${v.template_name}</td>
                                <td>${v.sent_time}</td>
                                <td><span class="badge ${code? 'badge-success' : 'badge-danger'}">${status}</span></td>
                                <td class="text-danger">${v.failure_reason || ''}</td>
                            </tr>
                        `);
                    })

                    $('#whatsappBroadcastTbl').dataTable({
                        stateSave: true,
                        responsive: true,
                        dom: 'Blfrtip',
                        buttons: ['csv', 'excel', 'print'],
                    });
                }
            })
            .fail((xhr,status,err) => {
                $('#whatsappBroadcastTbl tbody tr').remove();
            })
        },
    };
    
    $(Index.init);
</script>
@endsection
