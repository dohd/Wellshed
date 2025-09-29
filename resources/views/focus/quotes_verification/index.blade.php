@extends ('core.layouts.app')

@section ('title', 'Job Verification')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Verification Management</h4>
        </div>   
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.rjcs.index') }}" class="btn btn-success">
                            <i class="fa fa-list-alt"></i> Rjc
                        </a>                         
                    </div>
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
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                        <option value=""></option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                        <option value=""></option>
                                    </select>
                                </div> 
                                <div class="col-md-2">
                                    <select name="verify_state" id="verify_state" class="custom-select">
                                        <option value="">-- Select Status--</option>
                                        @foreach (['yes' => 'verified', 'no' => 'unverified'] as $key => $val)
                                            <option value="{{ ucfirst($key) }}">{{ ucfirst($val) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- <div class="col-md-2">
                                    <input type="text" id="quote_project" class="form-control" placeholder="Search Quote / Project">
                                </div> -->
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-2">{{ trans('general.search_date')}} </div>
                                <div class="col-2">
                                    <input type="text" name="start_date" id="start_date" class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control datepicker form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>
                            </div>
                            <hr>
                            <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th width="10%">Date</th>
                                        <th># Quote / PI</th>
                                        <th>{{ trans('customers.customer') }}</th>
                                        <th>Title</th>                                            
                                        <th>{{ trans('general.amount') }}</th>
                                        <th>Verified</th>
                                        <th>Project No</th>
                                        <th>LPO No</th>
                                        <th>Client Ref</th>
                                        <th>Profit & Percentage</th>
                                        <th>{{ trans('labels.general.actions') }}</th>
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
        ajaxSetup: {headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customerFilter").val()}),
                processResults: (data) => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) };
                },
            }
        }
    };

    let ajaxRequest;
    let isFallbackSearch;

    const Index = {
        init() {
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customerFilter').select2({allowClear: true}).change(Index.onChangeCustomer);
            $('#branchFilter').select2(config.branchSelect).change(Index.onChangeBranch);

            $('#verify_state').change(Index.verifyStateChange);
            $('#search').click(Index.searchDateClick);

            setTimeout(() => Index.drawDataTable(), 500);
        },

        verifyStateChange() {
            const el = $(this);
            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable({verify_state: el.val()});
        },

        searchDateClick() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const verifyState = $('#verify_state').val();
            if (!startDate || !endDate) return alert("Date range required!"); 

            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable({
                start_date: startDate, 
                end_date: endDate,
                verify_state: verifyState
            });
        },

        onChangeCustomer() {
            $("#branchFilter option:not(:eq(0))").remove();
            $('#quotesTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        onChangeBranch() {
            $('#quotesTbl').DataTable().destroy();
            Index.drawDataTable(); 
        },

        drawDataTable(params={}) {
            let table = $('#quotesTbl').DataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                ajax: function (data, callback, settings) {
                    if (ajaxRequest) ajaxRequest.abort();
                    ajaxRequest = $.ajax({
                        url: '{{ route("biller.quotes.get_project") }}',
                        method: 'POST',
                        data: {
                            pi_page: location.href.includes('page=pi') ? 1 : 0,
                            customer_id: $("#customerFilter").val(),
                            branch_id: $("#branchFilter").val(),
                            ...params,
                        },
                        success: function (response) {
                            // Populate DataTable
                            callback(response); 
                        },
                        error: function (xhr, status, error) {
                            if (status === 'abort') {
                                console.log('Previous DataTable AJAX request aborted.');
                            } else {
                                console.error('DataTable load error:', error);
                            }
                        }
                    })
                },
                columns: [{data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'date', 'tid', 'customer', 'notes', 'total', 'verified_total', 'project_tid', 'lpo_number', 'client_ref','expenses'
                    ].map(v => ({data:v, name: v})),    
                    {data: 'actions', name: 'actions', searchable: false, sortable: false},
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [4, 5] },
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: [ 'csv', 'excel', 'print']
            });

            // Listen to search event
            table.on('search.dt', function () {
                // Avoid loop
                if (isFallbackSearch) return;

                let searchTerm = table.search();
                let filteredCount = table.rows({ filter: 'applied' }).data().length;

                if (filteredCount === 0 && searchTerm) {
                    console.log('No results found. Searching backend for:', searchTerm);

                    // Set flag to true to avoid re-triggering
                    isFallbackSearch = true;
                    
                    $.ajax({
                        url: '{{ route("biller.quotes.get_project") }}',
                        method: 'POST',
                        data: { term: searchTerm },
                        success: function (response) {
                            if (response.data && response.data.length > 0) {
                                // Clear the table and add new data
                                table.clear();
                                table.rows.add(response.data);
                                table.draw();
                            } else {
                                console.log('No results from backend either.');
                            }
                            // Reset flag after update
                            isFallbackSearch = false;
                        },
                        error: function (xhr) {
                            console.error('AJAX search error:', xhr);
                            // Reset flag after update
                            isFallbackSearch = false;
                        }
                    });
                }

                if (!searchTerm && isFallbackSearch === false) {
                    // Restore original dataset
                    table.ajax.reload();

                    // Set flag to true to avoid re-triggering
                    isFallbackSearch = true;
                }
            });
        }
    };

    $(Index.init);
</script>
@endsection