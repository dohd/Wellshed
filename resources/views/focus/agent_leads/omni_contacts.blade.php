@extends ('core.layouts.app')
@section ('title', 'Bot Contacts')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Bot Contacts</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <select class="custom-select" id="source">
                                            <option value="">-- Select Source --</option>
                                            @foreach (['whatsapp', 'facebook', 'instagram', 'website'] as $item)
                                                <option value="{{ $item }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <hr>
                                <table id="contactsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Phone No.</th>
                                            <th>Source</th>
                                            <th>Last Converse</th>
                                            <th>Country</th>                                            
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
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Index = {
        init() {
            $('#source').change(Index.filterChange);
            Index.drawData();
        },

        filterChange() {
            $('#contactsTbl').DataTable().destroy();
            return Index.drawData();
        },

        drawData() {
            $('#contactsTbl').dataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                language: {@lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.agent_leads.omni_contacts_get") }}',
                    type: 'POST',
                    data: {
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                        source: $('#source').val(),
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'username', 'phone_no', 'user_type',  'last_converse', 'country'
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init)
</script>
@endsection
