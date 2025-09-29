@extends ('core.layouts.app')

@section ('title', 'WH Certificate management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">WH Certificate  Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.withholdings.partials.withholdings-header-buttons')
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
                            <table id="withholdingsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>WH No.</th>
                                        <th>Customer</th>
                                        <th>Cert Serial No.</th>
                                        <th>Cert Date</th>
                                        <th>Amount</th>
                                        <th>Allocated Amt.</th>
                                        <th>Note</th>
                                        <th>Invoice</th>
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    
    const dataTable = $('#withholdingsTbl').dataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        stateSave: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: '{{ route("biller.withholdings.get") }}',
            type: 'post'
        },
        columns: [
            {data: 'DT_Row_Index', name: 'id'},
            {data: 'tid', name: 'tid'},
            {data: 'customer', name: 'customer'},
            {data: 'reference', name: 'reference'},
            {data: 'cert_date', name: 'cert_date'},
            {data: 'amount', name: 'amount'},
            {data: 'allocate_ttl', name: 'allocate_ttl'},
            {data: 'note', name: 'note'},
            {data: 'invoice_tid', name: 'invoice_tid'},
            {data: 'actions', name: 'actions', searchable: false, sortable: false}
        ],
        columnDefs: [
            { type: "custom-number-sort", targets: [5] },
            { type: "custom-date-sort", targets: [6] }
        ],
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['csv', 'excel', 'print'],
    });
</script>
@endsection
