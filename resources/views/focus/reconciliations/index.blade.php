@extends ('core.layouts.app')

@section ('title',  'Reconciliations Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Reconciliations Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.reconciliations.partials.reconciliations-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <!-- Filters -->
        <div class="card">
            <div class="card-content">
                <div class="card-body mb-0 pb-0">
                    <div class="row">
                        <div class="col-md-3 col-12">
                            <select class="custom-select" id="account" data-placeholder="Search Account">
                                <option value=""></option>
                                @foreach ($accounts as $row)
                                    <option value="{{ $row->id }}">{{ $row->number }} - {{ $row->holder }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 col-12">
                            <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;Ending</span>
                              </div>
                              <input type="text" class="form-control" id="end_date">
                            </div>
                        </div>

                        <div class="col-md-2 col-12">
                            <select class="custom-select" id="status" data-placeholder="">
                                <option value="">-- Select Status --</option>
                                @foreach (['Reconciled', 'Uncleared'] as $row)
                                    <option value="{{ $row }}">{{ $row }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="reconciliationTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Account</th>
                                        <th>Statement Ending</th>
                                        <th>Ending Balance</th>
                                        <th>Difference</th>
                                        <th>Reconciled On</th>
                                        <th>Status</th>
                                        <th>Actions</th>
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $('#account').select2({allowClear: true});
    $('#end_date').datepicker({
        autoHide: true,
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        format: 'MM-yyyy',
        onClose: function(dateText, inst) { 
            $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
        }
    })
    .attr('placeholder', "{{ date('m-Y') }}");

    drawData();
    $('#account, #end_date, #status').change(function() {
        $('#reconciliationTbl').DataTable().destroy();
        drawData();
    });

    function drawData() {
        $('#reconciliationTbl').dataTable({
            stateSave: true,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: '{{ route("biller.reconciliations.get") }}',
                type: 'post',
                data: {
                    account_id: $('#account').val(),
                    end_date: $('#end_date').val(),
                    status: $('#status').val(),
                },
                dataSrc: ({data}) => {
                    if ($('#status').val()) {
                        data = (data || []).filter(function(v) {
                            return v.status_text == $('#status').val();
                        });
                    }
                    return data;
                },
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                ...['account', 'end_date', 'end_balance', 'balance_diff', 'reconciled_on', 'status'].map(v => ({data:v, name: v})),
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                },            
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });        
    }

</script>
@endsection