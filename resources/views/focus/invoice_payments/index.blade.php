@extends ('core.layouts.app')

@section ('title', 'Invoice Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Invoice Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoice_payments.partials.invoice-payment-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <label for="customer">Customer</label>
                                <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company ?: $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="project">Project</label>
                                <select id="project" class="custom-select" data-placeholder="Choose Project">
                                    <option value=""></option>
                                    @foreach ($projects as $row)
                                        <option value="{{ $row->id }}">{{ gen4tid('PRJ-', $row->tid) }}: {{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="amount">Total Amount (Ksh.)</label>
                                <input type="text" id="amount_total" class="form-control" readonly>
                            </div>                            
                            <div class="col-2">
                                <label for="unallocate">Total Unallocated (Ksh.)</label>
                                <input type="text" id="unallocated_total" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">   
                        <div class="row">
                            <div class="col-md-2">{{ trans('general.search_date')}} </div>
                            <div class="col-md-2">
                                <input type="text" name="start_date" id="start_date" class="date30 form-control form-control-sm datepicker">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                            </div>
                            <div class="col-md-2">
                                <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                            </div>
                        </div>
                        <hr>                        
                        <table id="paymentTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>PMT No</th>   
                                    <th>Customer</th>                                 
                                    <th>Account</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Unallocated</th>
                                    <th>PMT Mode</th>
                                    <th>Inv No</th>
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
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#project').select2({allowClear: true});

            $('#customer').change(Index.customerChange);
            $('#project').change(Index.projectChange);
            $('#search').click(Index.searchClick);
            Index.drawDataTable();
        },

        searchClick() {
            Index.startDate = $('#start_date').val();
            Index.endDate =  $('#end_date').val();
            if (!Index.startDate || !Index.endDate ) 
                return alert("Date range is Required");

            $('#paymentTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        customerChange() {
            $('#paymentTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        projectChange() {
            $('#paymentTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable(customer_id = '') {
            $('#paymentTbl').dataTable({
                processing: true,
                stateSave: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.invoice_payments.get') }}",
                    type: 'POST',
                    data: {
                        customer_id: $('#customer').val(), 
                        project_id: $('#project').val(),
                        start_date: Index.startDate, 
                        end_date: Index.endDate,
                    },
                    dataSrc: ({data}) => {
                        $('#amount_total').val('');
                        $('#unallocated_total').val('');
                        if (data.length) {
                            const aggregate = data[0].aggregate;
                            $('#amount_total').val(aggregate.amount_total);
                            $('#unallocated_total').val(aggregate.unallocated_total);
                        }
                        return data;
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    ...[
                        'tid', 'customer_id', 'account', 'date', 'amount', 'unallocated', 'payment_mode', 'invoice_tid', 
                    ].map(v => ({data: v, name: v})),
                    {data: 'actions',name: 'actions',searchable: false,sortable: false }
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [4, 5] },
                    { type: "custom-date-sort", targets: 3 }
                ],
                orderBy: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init);
</script>
@endsection