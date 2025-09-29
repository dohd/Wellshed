@extends ('core.layouts.app')
@section ('title', 'Potential Clients Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Potential Clients Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.leads.partials.leads-header-buttons')
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
                            <table id="leads-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Ticket No</th>
                                        <th>Client Name</th>
                                        <th>Client Contact</th>
                                        <th>Client Email</th>
                                        <th>Client Address</th>
                                        {{-- <th>{{ trans('labels.general.actions') }}</th> --}}
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
        startDate: '',
        endDate: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            $('#status').select2({allowClear: true});
            $('#category').select2({allowClear: true});
            $('#source').select2({allowClear: true});
            $('#classlist').select2({allowClear: true});

            $('#search').click(Index.filterChange);
            $('#status, #category, #source').on('change', Index.filterChange);
            $('#start_date, #end_date').on('change', Index.dateChange);
            Index.drawData();
        },

        filterChange() {
            $('#leads-table').DataTable().destroy();
            Index.drawData();
        },

        dateChange() {
            if (!$('#start_date').val() && !$('#end_date').val()) {
                return alert('Date Between Range is required!');
            }
            Index.startDate = $('#start_date').val();
            Index.endDate = $('#end_date').val();
        },

        drawData() {
            $('#leads-table').dataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                language: {@lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.leads.get_potentials") }}',
                    type: 'post',
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'lead', 'client_name', 'client_contact','client_email',
                    ].map(v => ({data: v, name: v})),
                    {
                        data: 'client_address',
                        name: 'client_address',
                        searchable: false,
                        sortable: false
                    }
                ],
                columnDefs: [
                    { type: "custom-date-sort", targets: [2] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    }
    $(Index.init);
</script>
@endsection