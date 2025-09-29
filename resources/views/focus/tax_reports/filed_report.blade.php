@extends ('core.layouts.app')

@section('title', 'Filed Tax Returns')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Filed Tax Returns</h4>
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
                                    {{ Form::text('record_month', request('record_month'), ['class' => 'form-control datepicker', 'id' => 'record_month']) }}
                                </div>
                                <div class="col-3">
                                    <label for="return_month">Return Month</label>
                                    {{ Form::text('return_month', request('return_month'), ['class' => 'form-control datepicker', 'id' => 'return_month']) }}
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
                                            <option value="{{ intval($key) }}" {{ in_array(request('tax_group'), ['16', '8', '0']) && intval($key) == request('tax_group')? 'selected' : '' }}>{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>

                            {{-- tab menu --}}
                            <ul class="nav nav-tabs nav-top-border no-hover-bg" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Sales</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Purchases</a>
                                </li>                                     
                            </ul>
                            <div class="tab-content px-1 pt-1">
                                {{-- sales tab --}}
                                <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                    
                                    <table id="saleTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                {{-- <th>#</th> --}}
                                                <th>Pin</th>
                                                <th>Buyer</th>
                                                <th>ETR Code</th>
                                                <th>Invoice Date</th>
                                                <th>CU Invoice No.</th>
                                                <th>Description</th>
                                                <th>Taxable Amount</th>
                                                <th>&nbsp;</th>
                                                <th>Invoice No.</th>
                                                <th>CN Invoice Date</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>                      
                                    </table>
                                </div>
                                {{-- purchases tab --}}
                                <div class="tab-pane" id="active2" aria-labelledby="link-tab2" role="tabpanel">
                                    
                                    <table id="purchaseTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                {{-- <th>#</th> --}}
                                                <th>Source</th>
                                                <th>Pin</th>
                                                <th>Supplier</th>
                                                <th>Invoice Date</th>
                                                <th>Invoice No.</th>
                                                <th>Description</th>
                                                <th>&nbsp;</th>
                                                <th>Taxable Amount</th>
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
        taxReportId: @json(request('tax_report_id')),
        recordMonth: @json(request('record_month')),
        returnMonth: @json(request('record_month')),
        taxGroup: @json(request('tax_group')),

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
                Index.reloadDataTable();
            });
            $('#tax_group').change(function() {
                Index.reloadDataTable();
            });

            this.drawSaleDataTable();
            this.drawPurchaseDataTable();
        },

        reloadDataTable() {
            $('#saleTbl').DataTable().destroy();
            $('#purchaseTbl').DataTable().destroy();
            Index.drawSaleDataTable();
            Index.drawPurchaseDataTable();
        },

        drawSaleDataTable() {
            $('#saleTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.tax_reports.get_filed_items') }}",
                    type: 'POST',
                    data: {
                        record_month: $('#record_month').val(),
                        return_month: $('#return_month').val(),
                        tax_group: $('#tax_group').val(),
                        tax_report_id: Index.taxReportId,
                        is_sale: 1, 
                        is_purchase: 0, 
                    },
                    dataSrc: ({data}) => {
                        data = data.map(v => {
                            v['etr_code'] = @json($company->etr_code);
                            return v;
                        });
                        return data;
                    },
                },
                columns: [
                    ...['pin', 'customer', 'etr_code', 'invoice_date', 'cu_invoice_no', 'note', 'subtotal',
                        'empty_col', 'invoice_no', 'cn_invoice_date',
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[3, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });
        },

        drawPurchaseDataTable() {
            $('#purchaseTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.tax_reports.get_filed_items') }}",
                    type: 'POST',
                    data: {
                        record_month: $('#record_month').val(),
                        return_month: $('#return_month').val(),
                        tax_group: $('#tax_group').val(),
                        tax_report_id: Index.taxReportId,
                        is_purchase: 1, 
                        is_sale: 0, 
                    },
                },
                columns: [
                    ...['source', 'pin', 'supplier', 'invoice_date', 'invoice_no', 'note', 
                        'empty_col', 'subtotal',
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[3, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
