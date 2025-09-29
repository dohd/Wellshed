@extends ('core.layouts.app')

@section('title', 'Supplier Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Goods Receive Note</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.utility_bills.partials.utility_bills-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.utility_bills.create', 'method' => 'GET', 'id' => 'grnSelectForm']) }}
                                <div class="form-group row">
                                    <div class="col-2">
                                        {{ Form::hidden('row_ids', null, ['id' => 'rowId']) }}
                                        {{ Form::submit('Generate Bill', ['class' => 'btn btn-success mt-2', 'id' => 'submitBtn']) }}
                                    </div>
                                    <div class="col-3">
                                        <label for="supplier">Supplier</label>
                                        <select name="supplier_id" id="supplier" class="custom-select">
                                            <option value="">-- Select Supplier --</option>
                                            @foreach ($suppliers as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="grnTbl" class="table table-striped table-bordered zero-configuration" width="100%" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox"  class="check-rows"></th>
                                        <th>GRN No.</th>
                                        <th>Supplier</th>
                                        <th>Purchase Type</th>
                                        <th>Dnote</th>
                                        <th>Date</th>
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
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        rowIds: [],
        supplier_id: '',

        init() {
            this.drawDataTable();
            $('#grnTbl').on('change', '.check-rows, .check-row', this.tableCheckRows);
            $('#submitBtn').click(this.formSubmit);
            $('#supplier').change(this.supplierChange);
        },

        formSubmit(e) {
            e.preventDefault();
            if (!$('#supplier').val()) return swal('Please choose supplier!');
            if (!Index.rowIds.length) return swal('Please select records!');
           
            $('#rowId').val(Index.rowIds.join(','));
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => $('#grnSelectForm').submit()); 
        },

        tableCheckRows() {
            const el = $(this);
            const isChecked = el.prop('checked');

            if (el.is('.check-row')) {
                const i = Index.rowIds.indexOf(el.val());
                if (isChecked) Index.rowIds.push(el.val());
                else Index.rowIds.splice(i, 1);
            } else {
                if (isChecked) {
                    $('#grnTbl tbody tr').each(function() {
                        $(this).find('.check-row').prop('checked', true).change();
                    });
                } else {
                    $('#grnTbl tbody tr').each(function() {
                        $(this).find('.check-row').prop('checked', false).change();
                    });
                }
            }
        },

        supplierChange() {
            Index.supplier_id = $(this).val();
            $('#grnTbl').DataTable().destroy();
            return Index.drawDataTable();
        },
        

        drawDataTable() {
            $('#grnTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.utility_bills.get_goodsreceivenote') }}",
                    type: 'POST',
                    data: {supplier_id: Index.supplier_id}
                },
                columns: [
                    {data: 'mass_select', name: 'mass_select', searchable: false,  sortable: false},
                    {data: 'tid', name: 'tid'},
                    {data: 'supplier', name: 'supplier'},
                    {data: 'purchase_type', name: 'purchase_type'},
                    {data: 'dnote', name: 'dnote'},
                    {data: 'date', name: 'date'},
                    {data: 'note', name: 'note'},
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'print', 'excel'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
