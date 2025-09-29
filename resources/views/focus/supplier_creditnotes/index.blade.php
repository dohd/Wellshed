@extends('core.layouts.app')

@section('title', $is_debit ? 'Debit Notes Management' : 'Supplier Credit Notes Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $is_debit ? 'Debit Notes Management' : 'Supplier Credit Notes Management' }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.supplier_creditnotes.partials.supplier_creditnotes-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center" style="max-width: 15em">{{ trans('general.search_date')}} </div>
                        <div class="col-md-1">
                            <input type="text" placeholder="{{ date('d-m-Y') }}" id="start_date" class="form-control form-control-sm datepicker">
                        </div>
                        <div class="col-md-1">
                            <input type="text" placeholder="{{ date('d-m-Y') }}" id="end_date" class="form-control form-control-sm datepicker">
                        </div>
                        <div class="col-md-1">
                            <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                        </div>
                    </div>
                    <hr>
                    <table id="supplier_creditnotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>#{{ $is_debit? 'DN' : 'CN' }} No.</th>
                                <th>Supplier</th>
                                <th>#Bill No | GRN No</th>
                                <th>Date</th>  
                                <th>Net Amount</th>
                                <th>VAT</th>
                                <th>Gross Amount</th>
                                <th>Note</th>
                                <th>Action</th>                                                                           
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true});

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } });
    setTimeout(() => drawData(), "{{ config('master.delay') }}");

    $('#search').click(function() {
        if (!$('#start_date').val() || !$('#end_date').val()) {
            return alert('Search Date Range is required!');
        }
        $('#supplier_creditnotesTbl').DataTable().destroy();
        return drawData();
    });

    function drawData() {
        $('#supplier_creditnotesTbl').dataTable({
            processing: true,
            responsive: true,
            language: {@lang("datatable.strings")},
            ajax: {
                url: "{{ route('biller.supplier_creditnotes.get') }}",
                type: 'post',
                data: {
                    is_debit: "{{ $is_debit ? 1 : 0 }}",
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                }
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                ...['tid', 'supplier', 'bill_no', 'date', 'subtotal', 'tax', 'total', 'note'].map(v => ({data: v, name: v})),
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }            
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: [4] },
                { type: "custom-date-sort", targets: [5] }
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'], 
        });
    }
</script>
@endsection