@extends ('core.layouts.app')

@section('title', 'Tax Return Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tax Return Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tax_reports.partials.tax-report-header-buttons')
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
                                <div class="col-3">
                                    <label for="record_month">Sale / Purchase Month</label>
                                    {{ Form::text('record_month', null, ['class' => 'form-control datepicker', 'id' => 'record_month']) }}
                                </div>
                                <div class="col-3">
                                    <label for="tax_group">Tax Group</label>
                                    @php
                                        $options = [
                                            '16' => 'General Rated Sales/Purchases (16%)',
                                            '8' => 'Other Rated Sales/Purchases (8%)',
                                            '0' => 'Zero Rated Sales/Purchases (0%)',
                                            '00' => 'Exempted Rated Sales/Purchases',
                                        ]
                                    @endphp
                                    <select name="tax_group" id="tax_group" class="custom-select">
                                        <option value="">-- select tax group --</option>
                                        @foreach ($options as $key => $val)
                                            <option value="{{ intval($key) }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label for="return_month">Return Month</label>
                                    {{ Form::text('return_month', null, ['class' => 'form-control datepicker', 'id' => 'return_month']) }}
                                </div>
                            </div>
                            <hr>
                            <table id="taxReportTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Purchase / Sale Month</th>
                                        <th>Return Month</th>
                                        <th>Tax Group</th>
                                        <th>Sale Tax</th>
                                        <th>Purchase Tax</th>
                                        <th>Note</th>
                                        <th>Created At</th>
                                        <th>Return No.</th>
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
    };

    const Index = {
        init() {
            // month picker
            $('.datepicker').datepicker({
                autoHide: true,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                format: 'MM-yyyy',
                onClose: function(dateText, inst) { 
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }
            });

            $('.datepicker').change(function() {
                $('#taxReportTbl').DataTable().destroy();
                Index.drawDataTable();
            });

            $('#tax_group').change(function() {
                $('#taxReportTbl').DataTable().destroy();
                Index.drawDataTable();
            });

            this.drawDataTable();
        },

        drawDataTable() {
            $('#taxReportTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.tax_reports.get') }}",
                    type: 'POST',
                    data: {
                        record_month: $('#record_month').val(), 
                        return_month: $('#return_month').val(),
                        tax_group: $('#tax_group').val(),
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'record_month', name: 'record_month'},
                    {data: 'return_month', name: 'return_month'},
                    {data: 'tax_group', name: 'tax_group'},
                    {data: 'sale_tax', name: 'sale_tax'},
                    {data: 'purchase_tax', name: 'purchase_tax'},
                    {data: 'note', name: 'note'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'return_no', name: 'return_no'},                    
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [3, 4] },
                    { type: "custom-date-sort", targets: [2] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
