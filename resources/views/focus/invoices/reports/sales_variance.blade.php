@extends ('core.layouts.app')
@section ('title', 'Sales Variance Report')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Sales Variance Report</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                {{-- @include('focus.invoices.partials.invoices-header-buttons') --}}
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row form-group">
                            <div class="col-3">
                                <label for="customer">Customer</label>
                                <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="project">Project</label>
                                <select id="project" class="custom-select" data-placeholder="Choose Project">
                                    <option value=""></option>
                                    @foreach ($projects as $row)
                                        <option value="{{ $row->id }}">{{ gen4tid('PRJ-', $row->tid) }} {{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="col-2">
                                <label for="invoice_status">Invoice Status</label>
                                <select name="invoice_status" id="inv_status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['not yet due', 'due'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="payment_status">Payment Status</label>
                                <select name="payment_status" id="pmt_status" class="custom-select">
                                    <option value="">-- select status --</option>
                                    @foreach (['unpaid', 'partially paid', 'paid'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="col-2">
                                <label for="invoice_category">Revenue Category</label>
                                <select class="custom-select" id="invoice_category">
                                <option value="">-- Select Category --</option>
                                @foreach ($accounts as $row)
                                    <option value="{{ $row->id }}" {{ $row->id == @$invoice->account_id ? 'selected' : '' }}>
                                        {{ $row->holder }}
                                    </option>
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
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <label for="amount">Total Amount</label>
                                    <input type="text" id="amount_total" class="form-control form-control-sm" readonly>
                                </div>                            
                                {{-- <div class="col-md-2">
                                    <label for="unallocate">Outstanding</label>
                                    <input type="text" id="balance_total" class="form-control form-control-sm" readonly>
                                </div> --}}
                            </div>
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
                            <table id="invoiceTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Invoice Date</th>
                                        <th>Invoice#</th>                                        
                                        <th>Customer</th>
                                        <th>Revenue Group</th>
                                        <th>Subject</th>
                                        <th>Due Date</th>
                                        <th>Net Amount</th>
                                        <th>VAT</th>
                                        <th>Gross Amount</th>
                                        <th>Gross Amount (FCY)</th>                                        
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
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        startDate: '',
        endDate: '',
        customerId: '',
        invoiceStatus: '',
        paymentStatus: '',
        invoiceCategory: '',

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});
            $('#project').select2({allowClear: true});
            Index.drawDataTable();

            $('#inv_status').change(Index.invoiceStatusChange);
            $('#pmt_status').change(Index.paymentStatusChange);
            $('#invoice_category').change(Index.invoiceCategoryChange);
            $('#customer').change(Index.customerChange);
            $('#project').change(Index.projectChange);
            $('#search').click(Index.searchClick);
        },

        searchClick() {
            Index.startDate = $('#start_date').val();
            Index.endDate =  $('#end_date').val();
            if (!Index.startDate || !Index.endDate ) 
                return alert("Date range is Required");

            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        invoiceStatusChange() {
            const lastOpt = $('#pmt_status option:eq(-1)');
            if ($(this).val() == 'due') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');
                
            Index.invoiceStatus = $(this).val();
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        paymentStatusChange() {
            const lastOpt = $('#inv_status option:eq(-1)');
            if ($(this).val() == 'paid') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');

            Index.paymentStatus = $(this).val();
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        invoiceCategoryChange() {
            Index.invoiceCategory = $(this).val();
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        customerChange() {
            Index.customerId = $(this).val();
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        projectChange() {
            $('#invoiceTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#invoiceTbl').dataTable({
                processing: true,
                stateSave: true,
                responsive: true,
                deferRender: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.sales_variance.get') }}",
                    type: 'POST',
                    data: {
                        start_date: Index.startDate, 
                        end_date: Index.endDate, 
                        customer_id: Index.customerId,
                        project_id: $('#project').val(),
                        invoice_category: Index.invoiceCategory
                    },
                    dataSrc: ({data}) => {
                        $('#amount_total').val('');
                        if (data.length) {
                            const total = data.reduce((init, current) => {
                                return init + accounting.unformat(current['total']);
                            }, 0);
                            $('#amount_total').val(accounting.formatNumber(total));
                            //$('#balance_total').val(aggregate.balance_total);
                        }
                        return data;
                    },
                },
                columns: [
                    ...[
                        'invoicedate', 'tid', 'customer',  'account', 'notes',  'invoiceduedate', 
                        'subtotal', 'tax', 'total', 'fx_total'
                    ].map(v => ({data: v, name: v})),
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [6, 7, 8] },
                    { type: "custom-date-sort", targets: [0] }
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