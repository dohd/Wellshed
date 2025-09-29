@extends ('core.layouts.app')

@section('title', 'Labour Allocation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Labour Allocation Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.labour_allocations.partials.labour_allocation-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-weight-bold d-inline">Total Payable Hours: &nbsp;</h5>                           
                        <span class="h3 totalHours">0</span>  
                        <div class="form-group row mt-2">
                            <div class="col-md-3">
                                <label for="employee">Search Customer</label> 
                                <select name="client_id" class="custom-select" id="client" data-placeholder="Search Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="record_month">Labour Month</label>
                                <input type="text" id="labour_month" class="form-control">
                            </div>                                
                            <div class="col-md-3">
                                <label for="employee">Search Employees</label>                             
                                <select class="custom-select" id="employee" data-placeholder="Choose Employee">
                                    <option value=""></option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>   
                            <div class="col-md-3">
                                <label for="casual">Search Casuals</label>                             
                                <select class="custom-select" id="casual" data-placeholder="Choose Casual">
                                    <option value=""></option>
                                    @foreach ($casuals as $casual)
                                        <option value="{{ $casual->id }}">{{ implode(' - ', array_filter([$casual->id_number, $casual->name])) }}</option>
                                    @endforeach
                                </select>
                            </div>                            
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
                            <table id="labour_allocationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Job Card</th>
                                        <th>#Project No</th>
                                        <th>Project Title</th>
                                        <th>#QT/PI No</th>
                                        <th>Customer - Branch</th>
                                        <th>Employees</th>
                                        <th>Reg. Hours</th>
                                        <th>OT. Hours</th>
                                        <th style="min-width: 100px;">Week Period</th>
                                        <th>Job Type</th>
                                        <th>Note</th>                                        
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        dateMonth: {
            autoHide: true,
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            format: 'MM-yyyy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
        },
    };

    const Index = {
        init() {
            $('#labour_month').datepicker(config.dateMonth);
            $('#client, #employee, #casual').select2({allowClear: true});
            $('#client, #employee, #casual, #labour_month').change(Index.onChangeFilter);
            Index.drawDataTable();
        },
        
        onChangeFilter() {
            $('#labour_allocationsTbl').DataTable().destroy();
            return Index.drawDataTable();   
        },

        drawDataTable() {
            $('#labour_allocationsTbl').dataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('biller.labour_allocations.get') }}",
                    type: 'POST',
                    data: {
                        client_id: $('#client').val(),
                        labour_month:  $('#labour_month').val(),
                        employee_id: $('#employee').val(),
                        casual_id: $('#casual').val(),
                    },
                    dataSrc: ({data}) => {
                        if (data.length) {
                            let payableHrs = 0;
                            data.forEach(v => {
                                if (v.is_payable) {
                                    payableHrs += v.hrs;
                                }
                            });
                            $('.totalHours').html(payableHrs);
                        }
                        return data;
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'date', name: 'date'},
                    {data: 'job_card', name: 'job_card'},
                    {data: 'tid', tid: 'tid'},
                    {data: 'project_name', name: 'project_name'},
                    {data: 'quote_tid', name: 'quote_tid'},
                    {data: 'customer_branch', name: 'customer_branch'},
                    {data: 'employee_name', name: 'employee_name'},
                    {data: 'hrs', name: 'hrs'},
                    {data: 'overtime_hrs', name: 'overtime_hrs'},
                    {data: 'period', name: 'period'},
                    {data: 'type', name: 'type'},
                    {data: 'note', name: 'note'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(Index.init);
</script>
@endsection
