@extends ('core.layouts.app')

@section ('title', 'View Import Request')

@section('page-header')
    <h1>
        <small>View Import Request</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Import Request</h3>

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
                            <div class="card-header">
                                @permission('import_request_approval')  
                                    <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                                    </a>
                                @endauth
                            </div>

                            <div class="card-content">

                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Supplier</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$import_request->supplier_name}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Note</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$import_request['notes']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ucfirst($import_request['status'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Remark</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$import_request['status_note']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Cost of buying items</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($import_request['item_cost'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Shipping Cost</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($import_request['shipping_cost'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Total Cost of buying & Shipping items</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($import_request['total'])}}</p>
                                        </div>
                                    </div>

                                    <input type="hidden" value="{{numberFormat($import_request->shipping_cost)}}" name="shipping_cost" id="shipping_cost" class="form-control">

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="row table-responsive">
                    <table id="importsTbl" class="table table-hover" cellspacing="0">
                        <thead>
                            <tr class="bg-gradient-directional-blue white">
                                <th style="width: 30px;">#</th>
                                <th style="width: 180px;">Product Name</th>
                                <th style="width: 80px;">Unit</th>
                                <th style="width: 200px;">Quantity</th>
                                <th style="width: 200px;">Rate (Cost of Item)</th>
                                <th style="width: 220px;">Amount</th>
                                <th style="width: 90px;">Rate %</th>
                                <th style="width: 110px;">Rate By Value</th>
                                <th style="width: 200px;">CBM / Weight</th>
                                <th style="width: 220px;">Total CBM</th>
                                <th style="width: 100px;">% of CBM</th>
                                <th style="width: 120px;">Value By CBM</th>
                                <th style="width: 150px;">Average (CBM value & Rate Value)</th>
                                <th style="width: 130px;">Avg (Rate & Shipping)</th>
                                <th style="width: 160px;">Landed Cost /Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @isset($import_request)
                                @foreach ($import_request->items as $k => $item)
                                    <tr>
                                        <td><span class="numbering">{{$k+1}}</span></td>
                                        <td>{{$item->product_name}}</td>
                                        <td>{{$item->unit}}</td> 
                                        <td>{{number_format($item->qty,2)}}</td>
                                        <td>{{number_format($item->rate,3)}}</td>
                                        <td>{{number_format($item->amount,3)}}</td>
                                        <td>{{number_format($item->rate_percent,2)}}</td>
                                        <td>{{number_format($item->rate_value,2)}}</td>
                                        <td style="width: 200px;">{{number_format($item->cbm, 5)}}</td>
                                        <td>{{number_format($item->total_cbm,5)}}</td>
                                        <td>{{number_format($item->cbm_percent,2)}}</td>
                                        <td>{{number_format($item->cbm_value,5)}}</td>
                                        <td>{{number_format($item->avg_cbm_rate_value,5)}}</td>
                                        <td>{{number_format($item->avg_rate_shippment,2)}}</td>
                                        <td>{{number_format($item->avg_rate_shippment_per_item,2)}}</td>
                                        <input type="hidden" name="product_id[]" id="productid-p{{$k}}" value="{{$item->product_id}}">
                                        <input type="hidden" name="id[]" class="id" value="{{$item->id}}">
                                        <input type="hidden" name="qty[]" id="qty-p{{$k}}" value="{{$item->qty}}" class="form-control qty"readonly>
                                        <input type="hidden" name="rate[]" id="rate-p{{$k}}" value="{{$item->rate}}" class="form-control rate">
                                        <input type="hidden" name="amount[]" id="amount-p{{$k}}" value="{{$item->amount}}" class="form-control amount" readonly>
                                        <input type="hidden" name="rate_percent[]" id="rate_percent-p{{$k}}" value="{{$item->rate_percent}}" class="form-control rate_percent" readonly>
                                        <input type="hidden" name="rate_value[]" id="rate_value-p{{$k}}" value="{{$item->rate_value}}" class="form-control rate_value" readonly>
                                        <td style="width: 200px;"><input type="hidden" name="cbm[]" id="cbm-p{{$k}}" value="{{$item->cbm}}" class="form-control cbm">
                                        <input type="hidden" name="total_cbm[]" id="total_cbm-p{{$k}}" value="{{$item->total_cbm}}" class="form-control total_cbm" readonly>
                                        <input type="hidden" name="cbm_percent[]" id="cbm_percent-p{{$k}}" value="{{$item->cbm_percent}}" class="form-control cbm_percent" readonly>
                                        <input type="hidden" name="cbm_value[]" id="cbm_value-p{{$k}}" value="{{$item->cbm_value}}" class="form-control cbm_value" readonly>
                                        <input type="hidden" name="avg_cbm_rate_value[]" id="avg_cbm_rate_value-p{{$k}}" value="{{$item->avg_cbm_rate_value}}" class="form-control avg_cbm_rate_value" readonly>
                                        <input type="hidden" name="avg_rate_shippment[]" id="avg_rate_shippment-p{{$k}}" value="{{$item->avg_rate_shippment}}" class="form-control avg_rate_shippment" readonly>
                                        <input type="hidden" name="avg_rate_shippment_per_item[]" id="avg_rate_shippment_per_item-p{{$k}}" value="{{$item->avg_rate_shippment_per_item}}" class="form-control avg_rate_shippment_per_item" readonly>
                                    </tr>
                                @endforeach
                                <tfoot>
                                    <tr>
                                        <td colspan="3">Totals</td>
                                        <td><span class="t_qty">0</span></td>
                                        <td><span class="t_rate">0</span></td>
                                        <td><span class="t_amount">0</span></td>
                                        <td><span class="t_rate_percent">0</span></td>
                                        <td><span class="t_rate_value">0</span></td>
                                        <td><span class="t_cbm">0</span></td>
                                        <td><span class="t_total_cbm">0</span></td>
                                        <td><span class="t_cbm_percent">0</span></td>
                                        <td><span class="t_cbm_value">0</span></td>
                                        <td><span class="t_avg_cbm_rate_value">0</span></td>
                                        <td><span class="t_avg_rate_shippment">0</span></td>
                                        <td><span class="t_avg_rate_shippment_per_item">0</span></td>
                                    </tr>
                                </tfoot>
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @include('focus.import_requests.partials.status')
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