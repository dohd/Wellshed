@extends ('core.layouts.app')

@section ('title', 'DLP & Moiety Retention')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">DLP & Moiety Retention</h4>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row form-group">
                            <div class="col-12 col-md-3">
                                <label for="customer">Customer</label>
                                <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="project">Project</label>
                                <select id="project" class="custom-select" data-placeholder="Choose Project">
                                    <option value=""></option>
                                    @foreach ($projects as $row)
                                        <option value="{{ $row->id }}">{{ gen4tid('PRJ-', $row->tid) }} {{ $row->name }}</option>
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
                                <div class="col-6 col-md-2">
                                    <label for="amount">Total Amount</label>
                                    <input type="text" id="amount_total" class="form-control form-control-sm" readonly>
                                </div>                            
                                {{-- <div class="col-md-2">
                                    <label for="unallocate">Outstanding</label>
                                    <input type="text" id="balance_total" class="form-control form-control-sm" readonly>
                                </div> --}}
                            </div>
                            <div class="row">
                                <div class="col-8 col-md-2 mb-1">{{ trans('general.search_date')}} </div>
                                <div class="col-8 col-md-2 mb-1">
                                    <input type="text" name="start_date" id="start_date" class="date30 form-control form-control-sm datepicker">
                                </div>
                                <div class="col-8 col-md-2 mb-1">
                                    <input type="text" name="end_date" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-8 col-md-2 mb-1">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table id="valuationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 80px;">Date</th>
                                            <th>Customer</th>
                                            <th>Valuation#</th>
                                            <th>BOQ/Quote/PI#</th>                                        
                                            <th>Invoice#</th>
                                            <th>Invoice Date</th>
                                            <th>Project#</th>
                                            <th>Project End</th>
                                            <th>DLP (Months)</th>
                                            <th>DLP End</th>
                                            <th>Retained Amount</th>
                                            <th>Retained %</th>
                                            <th>Status</th>
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

            $('#valuationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        invoiceStatusChange() {
            const lastOpt = $('#pmt_status option:eq(-1)');
            if ($(this).val() == 'due') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');
                
            Index.invoiceStatus = $(this).val();
            $('#valuationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        paymentStatusChange() {
            const lastOpt = $('#inv_status option:eq(-1)');
            if ($(this).val() == 'paid') {
                lastOpt.addClass('d-none');
            } else lastOpt.removeClass('d-none');

            Index.paymentStatus = $(this).val();
            $('#valuationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        invoiceCategoryChange() {
            Index.invoiceCategory = $(this).val();
            $('#valuationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        customerChange() {
            Index.customerId = $(this).val();
            $('#valuationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        projectChange() {
            $('#valuationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#valuationsTbl').dataTable({
                processing: true,
                stateSave: true,
                responsive: false,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.ipc_retention.get') }}",
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
                            {{-- const total = data.reduce((init, current) => {
                                return init + accounting.unformat(current['total']);
                            }, 0);
                            $('#amount_total').val(accounting.formatNumber(total)); --}}
                            //$('#balance_total').val(aggregate.balance_total);
                        }
                        return data;
                    },
                },
                columns: [
                    ...[
                        'date', 'customer', 'tid', 'quote_boq_tid', 'invoice_tid',  'invoiceduedate', 
                        'project_tid', 'project_end', 'dlp', 'dlp_end', 'retained_amount', 'retained_pcg',
                        'status'
                    ].map(v => ({data: v, name: v})),
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