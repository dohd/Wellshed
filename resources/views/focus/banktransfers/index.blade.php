@extends ('core.layouts.app')
@section ('title', 'Transfers management')

@section('content')
<div class="">
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title mb-0">Transfers Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.banktransfers.partials.banktransfers-header-buttons')
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
                                <table id="banktransfers-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>#Serial</th>
                                            <th>Date</th>
                                            <th>Transfer From</th>
                                            <th>Transfer Amount</th>
                                            <th>Transfer To</th>
                                            <th>Receipt Amount</th>
                                            <th>Note</th>
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
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    const dataTable = $('#banktransfers-table').dataTable({
        stateSave: true,
        processing: true,
        serverSide: true,
        responsive: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: '{{ route("biller.banktransfers.get") }}',
            type: 'post'
        },
        columns: [{
                data: 'DT_Row_Index',
                name: 'id'
            },
            ...['tid', 'transaction_date', 'source_account',  'credit', 'recepient_account', 'debit', 'note'].map(v => ({data:v, name:v})),
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }
        ],
        order: [[0, "desc"]],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['csv', 'excel', 'print']
    });
</script>
@endsection