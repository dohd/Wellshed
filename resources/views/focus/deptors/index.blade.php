@extends ('core.layouts.app')

@section ('title', 'Debtor | View')

@section('page-header')
    <h1>Debtors</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> Debtors List </h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        
                    </div>
                </div>
            </div>

            @if($segment)
                <div class="card">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <p>Customer </p>
                            </div>
                            <div class="col-sm-6">
                                <p>{{$customer->company}}  </p>
                            </div>
                        </div>
                      
                      
                            <div class="row">
                                <div class="col-sm-2">
                                    <p>Total Credit</p>
                                </div>
                                <div class="col-sm-6">
                                    <p>{{amountFormat($segment->sum('credit'))}}</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-2">
                                    <p>Total Debit</p>
                                </div>
                                <div class="col-sm-6">
                                    <p>{{amountFormat($segment->sum('debit'))}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">
                                    <p>Total Balance</p>
                                </div>
                                <div class="col-sm-6">
                                    <p>{{amountFormat($segment->sum('debit')-$segment->sum('credit'))}}</p>
                                </div>
                            </div>

                    </div>
                </div>


                   <div class="card p-1 bg-lighten-5">
                    <h4 class="mb-0">{{$customer->company}} Transactions </h4>
                    <table id="purchaseorders-table"
                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                           width="100%">
                        <thead>
                      <tr>
                                            <th>#</th>
                                            <th>Trans ID</th>
                                            <th>Trans Date</th>
                                            <th>Ref</th>
                                            <th>Customer</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                       
                                            <th>{{ trans('labels.general.actions') }}</th>

                                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
           
           
            @endif



            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">


                                    <table id="deptors-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Company</th>
                                            <th>Tax ID</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Balance</th>
                                            <th>{{ trans('general.createdat') }}</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i
                                                        class="fa fa-spinner spinner"></i></td>
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
    </div>
@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}

    <script>
        $(function () {
            setTimeout(function () {
                draw_data();
                sub_draw_data();
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#deptors-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("biller.deptors.get") }}',
                    type: 'post'
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'company', name: 'company'},
                    {data: 'taxid', name: 'taxid'},
                     {data: 'debit', name: 'debit'},
                    {data: 'credit', name: 'credit'},
                    {data: 'balance', name: 'balance'},
                     {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "asc"]],
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
            $('#deptors-table_wrapper').removeClass('form-inline');

        }



         function sub_draw_data() {
         $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#purchaseorders-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.purchases.get") }}',
                    type: 'post',
                      data: {rel_type: '3',rel_id:'{{request('rel_id',0)}}'}
                },
               columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'tid', name: 'tid'},
                    {data: 'trans_date', name: 'trans_date'},
                    {data: 'refer_no', name: 'refer_no'},
                    {data: 'supplier_id', name: 'supplier_id'},
                    {data: 'debit', name: 'debit'},
                    {data: 'credit', name: 'credit'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "asc"]],
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
        }
    </script>
@endsection
