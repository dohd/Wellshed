@extends ('core.layouts.app')

@section ('title', 'Customer Balances')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0"> Customer Balances </h2>
        </div>

    </div>


    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            <table id="clientBalanceTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>0 - 30 Days</th>
                                        <th>31 - 60 Days</th>
                                        <th>61 - 90 Days</th>
                                        <th>91 - 120 Days</th>
                                        <th> 120+ Days</th>
                                        <th> Aging Total </th>
                                        <th> Unallocated </th>
                                        <th> Balance </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#clientBalanceTable').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.client-balances.index") }}',
                type: 'GET',
                data: {

                }
            },
            columns: [
                {
                    data: 'tid',
                    name: 'tid'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: '0To30Days',
                    name: '0To30Days',
                    searchable: false,sortable: false
                },
                {
                    data: '31To60Days',
                    name: '31To60Days',
                    searchable: false,sortable: false
                },
                {
                    data: '61To90Days',
                    name: '61To90Days',
                    searchable: false,sortable: false
                },
                {
                    data: '91To120Days',
                    name: '91To120Days',
                    searchable: false,sortable: false
                },
                {
                    data: '120+Days',
                    name: '120+Days',
                    searchable: false,sortable: false
                },
                {
                    data: 'agingTotal',
                    name: 'agingTotal',
                    searchable: false,sortable: false
                },
                {
                    data: 'unallocated',
                    name: 'unallocated',
                    searchable: false,sortable: false
                },
                {
                    data: 'balance',
                    name: 'balance',
                    searchable: false,sortable: false
                },
            ],
            order: [
                [1, "asc"]
            ],
            lengthMenu: [
                [25, 50, 100, 200, -1],
                [25, 50, 100, 200, "All"]
            ],
            pageLength: -1,
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection