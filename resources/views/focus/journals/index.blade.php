@extends ('core.layouts.app')

@section ('title',  'Manual Journals')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Manual Journals</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.journals.partials.journals-header-buttons')
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
                            <div class="row mb-1">
                                <div class="col-md-2">{{ trans('general.search_date')}} </div>
                                <div class="col-md-2">
                                    <input type="text" name="start_date" value="{{ date('d-m-Y') }}" id="start_date" class="date30 form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="end_date" value="{{ date('d-m-Y') }}" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-md-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>                                
                            </div>
                            <hr>
                            <table id="journalsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Serial</th>
                                        <th>Date</th>
                                        <th>Note</th>
                                        <th>Amount</th>
                                        <th>Action</th>
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

            $('#search').click(Index.filterChange);
            $('#start_date, #end_date').on('change', Index.dateChange);
            Index.drawData();
        },

        filterChange() {
            $('#journalsTbl').DataTable().destroy();
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
            $('#journalsTbl').dataTable({
                stateSave: true,
                serverside: true,
                processing: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.journals.get") }}',
                    type: 'post',
                    data: {
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                    }
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    },            
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [4, 5] },
                    { type: "custom-date-sort", targets: [2] }
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