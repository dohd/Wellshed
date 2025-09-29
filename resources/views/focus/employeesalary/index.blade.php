@extends ('core.layouts.app')

@section ('title', 'Employee Salaries')

@section('page-header')
    <h1>Employee Salaries</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Employee Salaries</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.allowance.partials.allowances-header-buttons')
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
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                                               aria-selected="true">Current Salaries </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2" role="tab"
                                               aria-selected="false">Salary History</a>
                                        </li>
                                      
                            
                            
                                    </ul>
                                    <!--Active Salary-->
                                    <div class="tab-content px-1 pt-1">
                                        <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                                            <table id="salary-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Emp Number</th>
                                            <th>Emp Name</th>
                                            <th>Job Type</th>
                                            <th>Net Pay</th>
                                            <th>Basic Salary</th>
                                            <th>Paye</th>
                                            <th>SHIF</th>
                                            <th>NSSF</th>
                                            <th>Other Deduction</th>
                                            <th>Effective Date</th>
                                            <th>Contract Duration</th>
                                            <th>Contract End Date</th>
                                            
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
                                         <!---Salary History-->
                                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                                    </div>
                                    </div>


                                    
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
                draw_data()
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#salary-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.salaries.get") }}',
                    type: 'post'
                },
                columns: [
                    { data: '' },
                    {data: 'emp_no', name: 'emp_no'},
                    {data: 'name', name: 'name'},
                    {data: 'job_type', name: 'job_type'},
                    {data: 'net_pay', name: 'net_pay'},
                    {data: 'basic_salary', name: 'basic_salary'},
                    {data: 'paye', name: 'paye'},
                    {data: 'nhif', name: 'nhif'},
                    {data: 'nssf', name: 'nssf'},
                    {data: 'deductions', name: 'deductions'},
                    {data: 'effective_date', name: 'effective_date'},
                    {data: 'contact_duration', name: 'contact_duration'},
                    {data: 'contact_end_date', name: 'contact_end_date'},
                   
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
     
    
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
                },
                  // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Salary Details For ' + data['name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.columnIndex !== 6 // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');
            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
            });
            $('#salary-table_wrapper').removeClass('form-inline');

        }
    </script>
@endsection
