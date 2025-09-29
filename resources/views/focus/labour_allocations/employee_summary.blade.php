@extends ('core.layouts.app')

@section('title', 'Employee Report | Labour Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Employee Labour Report</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <h5 class="font-weight-bold d-inline">Total Payable Hours: &nbsp;</h5>                           
                            <span class="h3 totalHours">0</span>                            
                            <div class="form-group row mt-2">
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
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
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
                            <table id="labour_allocationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>#Project No</th>
                                        <th>Customer - Branch</th>
                                        <th>Project Title</th>
                                        <th>#QT/PI No.</th>
                                        <th>Employee</th>
                                        <th>Hrs</th>
                                        <th>Type</th>
                                        <th>Job Card</th>
                                        <th>Note</th>
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
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true},
    };

    const Index = {
        
        init() {
            // month picker
            $('#labour_month').datepicker({
                autoHide: true,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                format: 'MM-yyyy',
                onClose: function(dateText, inst) { 
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }
            });
            
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#employee, #casual').select2({allowClear: true});
            this.drawDataTable();
            
            $('#employee').change(this.onEmployeeChange);
            $('#labour_month').change(this.onLabourMonthChange);
            $('#search').click(this.searchDateClick);
        },
        
        onLabourMonthChange() {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#labour_allocationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },
        
        onEmployeeChange() {
            $('.hourlyRate').text(0);
            $('.amountPayable').text(0);
            setTimeout(() => {
                $.get("{{ route('biller.labour_allocations.employee_hourly_rate') }}?employee_id=" + this.value, data => {
                    const rate_per_hr = data.rate;
                    const total_hrs = accounting.unformat($('.totalHours').text());
                    const payable = rate_per_hr * total_hrs;
                    
                    $('.hourlyRate').text(accounting.formatNumber(rate_per_hr));
                    $('.amountPayable').text(accounting.formatNumber(payable));
                });
            }, 1000);
            
            $('#labour_allocationsTbl').DataTable().destroy();
            return Index.drawDataTable({
                employee_id: $('#employee').val()
            });   
        },

        searchDateClick() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            if (!$('#labour_month').val()) {
                if (!startDate || !endDate) return alert("Date range required!"); 
            }

            $('#labour_allocationsTbl').DataTable().destroy();
            return Index.drawDataTable({
                start_date: startDate, 
                end_date: endDate
            });
        },

        drawDataTable(params={}) {
            $('#labour_allocationsTbl').dataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.labour_allocations.get_summary') }}",
                    type: 'POST',
                    data: {
                        ...params,
                        labour_month: $('#labour_month').val(),
                    },
                     dataSrc: ({data}) => {
                        $('.totalHours').text('0');
                        if (data.length && data[0].aggregate) {
                            const aggr = data[0].aggregate;
                            $('.totalHours').text(aggr.total_hrs);
                        }
                        return data;
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'date', name: 'date'},
                    {data: 'tid', tid: 'tid'},
                    {data: 'customer', name: 'customer'},
                    {data: 'project_name', name: 'project_name'},
                     {data: 'main_quote_id', name: 'main_quote_id'},
                    {data: 'employee_name', name: 'employee_name'},
                    {data: 'hrs', name: 'hrs'},
                    {data: 'type', name: 'type'},
                    {data: 'job_card', name: 'job_card'},
                    {data: 'note', name: 'note'},
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
