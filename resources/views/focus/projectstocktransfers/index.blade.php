@extends ('core.layouts.app')

@section ('title', 'Stock Transfer')

@section('page-header')
    <h1>Stock Transfer</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Stock Transfer Management</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.projectstocktransfers.partials.projectstocktransfers-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            @if($segment)
                @php
                    $total=$segment->invoices->sum('total');
                    $paid=$segment->invoices->sum('pamnt');
                    $due=$total-$paid;
                @endphp
                <div class="card">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <p>{{$words['name']}} </p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{$words['name_data']}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">
                                <p>{{trans('customers.email')}}</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{$segment->email}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">
                                <p>{{trans('general.total_amount')}}</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{amountFormat($total)}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">
                                <p>{{trans('payments.paid_amount')}}</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{amountFormat($paid)}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">
                                <p>{{trans('general.balance_due')}}</p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{amountFormat($due)}}</p>
                            </div>
                        </div>

                    </div>
                </div>
            @endif
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    <table id="purchaseorders-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Trans ID</th>
                                            <th>Trans Date</th>
                                            <th>Ref</th>
                                            <th>Client</th>
                                            <th>Branch</th>
                                            <th>Project</th>
                                            <th>Amount</th>
                                            <th>{{ trans('labels.general.actions') }}</th>

                                        </tr>
                                        </thead>


                                        <tbody></tbody>
                                    </table>
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
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#purchaseorders-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                "lengthMenu": [[10, 25, 50,100,500,1000, -1], [10, 25, 50,100,500,1000, "All"]],
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.projectstocktransfers.get") }}',
                    type: 'post',
                    data: {rel_type: '1'}
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'tid', name: 'tid'},
                    {data: 'trans_date', name: 'trans_date'},
                    {data: 'refer_no', name: 'refer_no'},
                    {data: 'client_id', name: 'client_id'},
                    {data: 'branch_id', name: 'branch_id'},
                    {data: 'project_id', name: 'project_id'},
                    {data: 'credit', name: 'credit'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[1, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {extend: 'csv', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'excel', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'print', footer: true, exportOptions: {columns: [0, 1]}}
                    ]
                }
            });
            $('#purchaseorders-table_wrapper').removeClass('form-inline');

        });
    </script>
@endsection
