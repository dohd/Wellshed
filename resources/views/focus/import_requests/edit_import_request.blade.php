@extends ('core.layouts.app')

@section ('title', 'Edit Import Request')

@section('page-header')
    <h1>
        <small>Edit Import Request</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Import Request</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.import_requests.partials.import_requests-header-buttons')
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
                                    {{ Form::model($import_request, ['route' => ['biller.import_requests.update_import_request', $import_request], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group row">
                                        <div class="col-md-2">
                                            <label for="tid" class="caption">Import No.</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                                                {{ Form::text('tid', gen4tid("IMP-", @$import_request? $import_request->tid : $last_tid+1), ['class' => 'form-control round', 'disabled']) }}
                                                
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label for="supplier">Supplier</label>
                                            {{ Form::text('supplier_name', null, ['class' => 'form-control', 'id' => 'supplier_name', 'disabled' => 'disabled']) }}
                                        </div>
                                        
                                        
                                    </div>
                                    <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Import Items</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Expense</a>
                                        </li>
                                        
                                    </ul>
                                    <div class="tab-content px-1 pt-1">
                                        <!-- tab1 -->
                                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                                            <div class="card-content">
                                                <div class="table-responsive">

                                                    @include("focus.import_requests.partials.cbm_import_items")
                                                </div>
                                            </div>
                                        </div>
                                        <!-- tab2 -->
                                        <div class="tab-pane in" id="active2" aria-labelledby="active-tab2" role="tabpane2">
                
                                            <div class="card-content">
                                                <div class="card-body">
                                                    
                                                    @include("focus.import_requests.partials.expense_tab")
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        
                                        <div class="form-group row mt-3">
                                            <div class="col-9"></div>
                                            <div class="col-3">
                                                
                                                
                                                <label class="mb-0">Cost of buying items</label>
                                                <input type="text" name="item_cost" id="item_cost" class="form-control" readonly>
                                                <label for="shipping_cost">Total Shipping Cost</label>
                                                <input type="text" value="{{numberFormat($import_request->shipping_cost)}}" name="shipping_cost" id="shipping_cost" class="form-control">
                                                <label class="mb-0">Total Cost of buying & Shipping items
                                                </label>
                                                <input type="text" name="total" class="form-control" id="total" readonly>
                                                @php
                                                    $disabled = '';
                                                    if (isset($import_request) && $import_request->status == 'approved')
                                                        $disabled = 'disabled';
                                                @endphp
                                                {{ Form::submit('Generate', ['class' => 'btn btn-success btn-lg mt-1', $disabled]) }}
                                            </div>
                                        </div>
                                    </div><!--form-group-->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
            select2: {allowClear: true},
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        const Index = {
            init(){
                $.ajaxSetup(config.ajax);
               $('#importsTbl').on('change', '.rate', this.calculateRateValue);
               $('#importsTbl').on('change', '.cbm', this.calculateCBMValue);
               $('#expensesTbl').on('click', '.tick', this.ommitExpense);
               $('#shipping_cost').change(() => Index.calcTotal());
               $('#expensesTbl').on('change', '.exp_qty, .exp_rate, .currencys', this.calcExpenses);
               $('.currencys').each(function(){
                    $(this).trigger('change');
                });
               Index.calcTotal();
            },

            calculateShipmentCost(){
                let total_shipment_cost = 0;

                $('#expensesTbl tbody tr').each(function () {
                    const row = $(this);

                    // Get values even if inputs are disabled
                    const exp_qty = accounting.unformat(row.find('.exp_qty').val() || 0);
                    let exp_rate = accounting.unformat(row.find('.exp_rate').val() || 0);

                    // Try to get selected currency rate, fallback to input value if disabled
                    let fx_curr_rate = 1;

                    if (!row.find('.currencys').is(':disabled')) {
                        fx_curr_rate = accounting.unformat(row.find('.currencys option:selected').attr('rate')) || 1;
                        row.find('.fx_curr_rate').val(fx_curr_rate); // update input
                    } else {
                        fx_curr_rate = accounting.unformat(row.find('.fx_curr_rate').val()) || 1;
                        exp_rate = 0;
                    }

                    let shipment = 0;
                    if (fx_curr_rate > 1) {
                        const fx_rate = exp_rate * fx_curr_rate;
                        shipment = fx_rate * exp_qty;
                    } else {
                        shipment = exp_qty * exp_rate;
                    }

                    total_shipment_cost += shipment;
                });

                $('#shipping_cost').val(accounting.formatNumber(total_shipment_cost, 2));
                Index.calcTotal();
            },


            ommitExpense() {
                const el = $(this);
                const row = el.parents('tr:first');

                if (el.is('.tick')) {
                    if (el.is(':checked')) {
                        // Get current rate before disabling
                        const exp_qty = accounting.unformat(row.find('.exp_qty').val() || 0);
                        const exp_rate = accounting.unformat(row.find('.exp_rate').val() || 0);
                        const fx_curr_rate = accounting.unformat(row.find('.currencys option:selected').attr('rate')) || 1;

                        // Set fx_curr_rate explicitly
                        row.find('.fx_curr_rate').val(fx_curr_rate);

                        // You can also store computed shipment as a hidden input if needed
                        // let fx_rate = exp_rate * fx_curr_rate;
                        // let shipment = fx_curr_rate > 1 ? fx_rate * exp_qty : exp_qty * exp_rate;

                        // Disable inputs
                        row.find('.exp_qty, .exp_rate,.e_id, .expense_id, .lpo_expense_id, .exp_uom, .currencys, .fx_curr_rate, .fx_rate').attr('disabled', true);
                    } else {
                        // Enable inputs
                        row.find('.exp_qty, .exp_rate, .e_id, .expense_id, .lpo_expense_id, .exp_uom, .currencys, .fx_curr_rate, .fx_rate').attr('disabled', false);
                    }
                }

                Index.calculateShipmentCost();
            },


            calcExpenses(){
                const el = $(this);
                const row = el.parents('tr:first');
                const exp_qty = accounting.unformat(row.find('.exp_qty').val() || 0);
                const exp_rate = accounting.unformat(row.find('.exp_rate').val() || 0);
                let fx_curr_rate = accounting.unformat(row.find('.currencys option:selected').attr('rate'));
                row.find('.fx_curr_rate').val(fx_curr_rate)
                let fx_rate = 0;
                if(fx_curr_rate > 1){
                    fx_rate = exp_rate * fx_curr_rate;
                }
                let amount = exp_qty*exp_rate;
                row.find('.exp_amount').text(accounting.formatNumber(amount));
                row.find('.fx_rate').val(accounting.formatNumber(fx_rate));
                console.log(fx_curr_rate)
                Index.calculateShipmentCost();
            },

            
            calculateRateValue()
            {
                const el = $(this);
                const row = el.parents('tr:first');
                const qty = accounting.unformat(row.find('.qty').val());
                const rate = accounting.unformat(row.find('.rate').val());
                const shipping_cost = accounting.unformat($('#shipping_cost').val());
                let amount = qty*rate;
                row.find('.amount').val(amount);
                Index.calcTotal();
            },
            calculateCBMValue() {
                const el = $(this);
                const row = el.closest('tr');

                const qty = accounting.unformat(row.find('.qty').val() || 0);
                const cbm = accounting.unformat(row.find('.cbm').val() || 0);

                const total_cbm = qty * cbm;
                row.find('.total_cbm').val(total_cbm);

                // Just recalculate everything in one place
                Index.calcTotal();
            },

            calcTotal() {
                let t_qty = 0, t_rate = 0, 
                t_amount = 0, t_cbm = 0, t_total_cbm = 0, 
                t_cbm_percent = 0, t_cbm_value = 0, t_rate_percent = 0, 
                t_rate_value = 0, t_avg_cbm_rate_value = 0, t_avg_rate_shippment = 0, t_avg_rate_shippment_per_item = 0;

                // First pass: calculate totals
                const shipping_cost = accounting.unformat($('#shipping_cost').val());
                $('#importsTbl tbody tr').each(function () {
                    const row = $(this);
                    const qty = accounting.unformat(row.find('.qty').val() || 0);
                    const rate = accounting.unformat(row.find('.rate').val() || 0);
                    const cbm = accounting.unformat(row.find('.cbm').val() || 0);
                    
                    if (qty === 0) return;
                    
                    const amount = qty * rate;
                    const total_cbm = qty * cbm;
                    
                    row.find('.total_cbm').val(total_cbm); // Ensure this is also updated if not already

                    t_qty += qty;
                    t_rate += rate;
                    t_cbm += cbm;
                    t_amount += amount;
                    t_total_cbm += total_cbm;
                });

                // Second pass: update cbm_percent per row based on total CBM
                $('#importsTbl tbody tr').each(function () {
                    const row = $(this);
                    const qty = accounting.unformat(row.find('.qty').val() || 0);
                    const total_cbm_val = accounting.unformat(row.find('.total_cbm').val() || 0);
                    const tot_amount = accounting.unformat(row.find('.amount').val() || 0);
                    const cbm_percent = t_total_cbm > 0 ? (total_cbm_val / t_total_cbm) * 100 : 0;
                    const cbm_value = t_total_cbm > 0 ? (cbm_percent*shipping_cost) / 100: 0;
                    const rate_percent = t_amount > 0 ? (tot_amount / t_amount) * 100 : 0;
                    const rate_value = t_amount > 0 ? (rate_percent*shipping_cost) / 100: 0;
                    const avg_cbm_rate_value = t_amount > 0 ? (rate_value + cbm_value) / 2 : 0;
                    const avg_rate_shippment = t_amount > 0 ? (tot_amount + avg_cbm_rate_value) : 0;
                    const avg_rate_shippment_per_item = t_amount > 0 ? (tot_amount + avg_cbm_rate_value) / qty : 0;
                    
                    console.log(rate_percent, tot_amount);
                    row.find('.cbm_percent').val(cbm_percent.toFixed(2));
                    row.find('.cbm_value').val(cbm_value.toFixed(2));
                    row.find('.rate_percent').val(rate_percent.toFixed(2));
                    row.find('.rate_value').val(rate_value.toFixed(2));
                    row.find('.avg_cbm_rate_value').val(avg_cbm_rate_value.toFixed(2));
                    row.find('.avg_rate_shippment').val(avg_rate_shippment.toFixed(2));
                    row.find('.avg_rate_shippment_per_item').val(avg_rate_shippment_per_item.toFixed(2));
                    t_cbm_percent += cbm_percent;
                    t_cbm_value += cbm_value;
                    t_rate_percent += rate_percent;
                    t_rate_value += rate_value;
                    t_avg_cbm_rate_value += avg_cbm_rate_value;
                    t_avg_rate_shippment += avg_rate_shippment;
                    t_avg_rate_shippment_per_item += avg_rate_shippment_per_item;
                });

                // Update UI with overall totals
                $('.t_qty').text(Index.formatFloat(t_qty));
                $('.t_rate').text(Index.formatFloat(t_rate));
                $('.t_amount').text(Index.formatFloat(t_amount));
                $('.t_cbm').text(Index.formatFloat(t_cbm));
                $('.t_total_cbm').text(Index.formatFloat(t_total_cbm));
                $('.t_cbm_percent').text(Index.formatFloat(t_cbm_percent));
                $('.t_cbm_value').text(Index.formatFloat(t_cbm_value));
                $('.t_rate_percent').text(Index.formatFloat(t_rate_percent));
                $('.t_rate_value').text(Index.formatFloat(t_rate_value));
                $('.t_avg_cbm_rate_value').text(Index.formatFloat(t_avg_cbm_rate_value));
                $('.t_avg_rate_shippment').text(Index.formatFloat(t_avg_rate_shippment));
                $('.t_avg_rate_shippment_per_item').text(Index.formatFloat(t_avg_rate_shippment_per_item));
                $('#item_cost').val(Index.formatFloat(t_amount));
                $('#total').val(Index.formatFloat(t_amount) + Index.formatFloat(shipping_cost));

            },
            formatFloat(num) {
                num = parseFloat(num);
                return Number.isInteger(num) ? num : parseFloat(num.toFixed(4));
            }

        };
        $(()=>Index.init())
    </script>
@endsection