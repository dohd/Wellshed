@extends ('core.layouts.app')
@section ('title', 'Tickets Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tickets Management</h4>
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
                    <div class="card-body">
                        <div class="row no-gutters">
                            <div class="col-sm-3 col-md-2 h4">Open Tickets</div>
                            <div class="col-sm-2 col-md-1 h4 text-primary font-weight-bold">{{ $open_lead }}</div>
                            <div class="col-sm-12 col-md-1 h4 text-primary font-weight-bold">{{ numberFormat(div_num($open_lead, $total_lead) * 100) }}%</div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-sm-3 col-md-2 h4">Closed Tickets</div>
                            <div class="col-sm-2 col-md-1 h4 text-success font-weight-bold">{{ $closed_lead }}</div>
                            <div class="col-sm-12 col-md-1 h4 text-success font-weight-bold">{{ numberFormat(div_num($closed_lead, $total_lead) * 100) }}%</div>
                        </div>

                        <div class="row mt-2" id="filters">
                            <div class="col-2">
                                <label for="status">Status</label>
                                <select name="status" class="custom-select" id="status" data-placeholder="Filter by status">
                                    <option value=""> Filter by Status </option>
                                    @foreach (['Open' => 'OPEN', 'Closed' => 'CLOSED'] as $key => $value)
                                        <option value="{{ $value }}">{{ $key }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="category" class="caption">Category</label>
                                <select class="custom-select" name="account_id" id="category" data-placeholder="Filter by Income Category">
                                    <option value=""></option>
                                    @php $exclude = ['Stock Gain', 'Others', 'Point of Sale', 'Loan Penalty Receivable', 'Loan Interest Receivable'] @endphp
                                    @foreach ($income_accounts->whereNotIn('holder', $exclude) as $row)
                                        <option value="{{ $row->id }}">{{ $row->holder }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="source">Source</label>
                                <select name="source" class="custom-select" id="source" data-placeholder="Filter by source">
                                    <option value=""> Filter by Source </option>
                                    @foreach ($leadSources as $src)
                                        <option value="{{ $src['id'] }}">{{ $src['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="status">Tender Status</label>
                                <select class="custom-select" id="tenderStatus" data-placeholder="Filter by status">
                                    <option value=""> Filter by Status </option>
                                    @foreach ($tenderStatus as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if ($classlists->count())
                                <div class="col-2">
                                    <label for="classlist">Search Class</label>
                                    <select id="classlist" name="classlist_id" class="form-control" data-placeholder="Choose Class or Subclass">
                                        <option value=""></option>
                                        @foreach ($classlists as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }} {{ $item->parent_class? '('. $item->parent_class->name .')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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
                                <div class="col-md-2">
                                    {{ Form::open(['route' => ['biller.leads.download_walkins'], 'method' => 'get']) }} 
                                    <button type="submit" class="btn btn-sm btn-secondary">Download Walkins</button>
                                    {{ Form::close() }}    
                                </div>

                            </div><hr>
                            <table id="leads-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#Tkt. No</th>
                                        <th>Client & Branch</th>
                                        <th>Title</th>
                                        <th>Tkt. Status</th>
                                        <th>Tender Status</th>
                                        <th>Category</th>
                                        <th>Source</th>
                                        <th>Exp. Income</th>
                                        <th>New/Existing</th>
                                        <th>Created At</th>
                                        <th>Client Ref</th>
                                        <th>Created By</th>
                                        <th>Days to Event</th>
                                        <th>Actions</th>
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
            $('#status, #category, #source, #classlist, #tenderStatus').select2({allowClear: true});

            $('#search').click(Index.filterChange);
            $('#status, #category, #source, #tenderStatus').on('change', Index.filterChange);
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
                    url: '{{ route("biller.leads.get") }}',
                    type: 'post',
                    data: {
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                        status: $('#status').val(),
                        category: $('#category').val(),
                        source: $('#source').val(),
                        classlist_id: $('#classlist_id').val(),
                        tender_status: $('#tenderStatus').val(),
                    },
                },
                columns: [
                    ...[
                        'reference', 'client_name', 'title', 'status', 'tender_status', 'category', 'source', 'expected_income', 
                        'client_status', 'created_at', 'client_ref', 'creator', 'exact_date',
                    ].map(v => ({data: v, name: v})),
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                columnDefs: [
                    { type: "custom-date-sort", targets: [6] }
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