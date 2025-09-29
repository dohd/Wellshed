@extends ('core.layouts.app')
@section ('title', 'Bank Feeds')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-md-6 col-12">
            <h4 class="content-header-title mb-0">Bank Feeds</h4>
        </div>
    </div>
    <div class="content-body">
        <div class="row">
            <div class="col-3">
                <div class="card round">
                    <div class="card-content">
                        <div class="card-body p-1">
                            <h5 class="text-primary">NCBA Checking</h5>
                            <h5 class="text-primary font-weight-bold">{{ numberFormat($RTBalance) }}</h5>
                            <div><h5 class="text-primary d-inline">Bank Balance</h5><span class="float-right">Real-time</span></div>
                            <hr>
                            <h5 class="font-weight-bold">{{ numberFormat($balance) }}</h5>
                            <h5>Bank Balance</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-top-border no-hover-bg " role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">For Review</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Categorized</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Excluded</a>
                                </li> 
                            </ul>
                            <div class="tab-content px-1 pt-1">
                                <!-- For Review -->
                                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                    <table id="bankfeeds-tbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="checkAll"></th>
                                                <th>Date</th>
                                                <th>Narrative</th>
                                                <th>Payee</th>
                                                <th>Amount</th>
                                                <th>Spent</th>
                                                <th>Received</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <!-- End For Review -->

                                <!-- Categorized -->
                                <div class="tab-pane" id="active2" aria-labelledby="active-tab2" role="tabpanel">
                                    Categorized
                                </div>
                                <!-- End Categorized -->

                                <!-- Excluded -->
                                <div class="tab-pane" id="active3" aria-labelledby="active-tab3" role="tabpanel">
                                    Excluded
                                </div>
                                <!-- End Excluded -->
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
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        },
    };

    
    const Index = {
        init() {
            $.ajaxSetup(config.ajax);
            Index.drawDataTable();
        },

        drawDataTable() {
            $('#bankfeeds-tbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.bank_feeds.get") }}',
                    type: 'post'
                },
                columns: [
                    {data: 'row_check', name: 'row_check', sortable: false, searchable: false},
                    ...[
                        'trans_time', 
                        'narrative', 
                        'customer_name', 
                        'trans_amount',
                        'spent',
                        'received',
                    ].map(v => ({data: v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init);
</script>
@endsection
