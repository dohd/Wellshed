<div class="form-group row">
    <div class="col-6">
        <label for="quote">{{ @$quote->bank_id? '#Proforma Invoice' : '#Quote' }}</label>
        @php
            $quote_tid = gen4tid(@$quote->bank_id? 'PI-' : 'Qt-', @$quote->tid);
        @endphp
        {{ Form::text('quote', @$quote_tid . ' - '. @$quote->notes, ['class' => 'form-control', 'id' => 'reference', 'disabled']) }}
        {{ Form::hidden('quote_id', @$quote->id) }}
        <input type="hidden" name="client_branch" value="{{@$quote->notes}}" id="client_branch">
        <input type="hidden" data-proj="{{@$quote->id}}" value="{{@$project->name}}" id="proj">
    </div>
    <div class="col-2">
        <label for="tid">Issuance No.</label>
        {{ Form::text('tid', @$projectstock ? $projectstock->tid : $tid+1, ['class' => 'form-control', 'id' => 'tid', 'readonly']) }}
    </div>  
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div> 
    <div class="col-2">
        <label for="reference">Reference</label>
        {{ Form::text('reference', null, ['class' => 'form-control', 'placeholder' => 'Requisition No.', 'id' => 'reference', 'required']) }}
    </div> 
</div> 
<div class="form-group row">  
    <div class="col-12">
        <label for="note">Note</label>    
        {{ Form::text('note', null, ['class' => 'form-control', 'placeholder' => 'Additional Remarks e.g Technician names', 'id' => 'note', 'required']) }}
    </div>                          
</div>


<div class="table-responsive">
    <table class="table tfr my_stripe_single" id="productsTbl">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Product</th>
                <th>UoM</th>
                <th>Product Code</th>
                <th>Qty Approved</th>
                <th>Qty Issued</th>
                <th>Requisition Approved</th>
                <th>Warehouse</th>
                <th width="10%">Issue Qty</th>
                <th width="10%">Requisition Qty</th>
                <th>Requisition Check</th>
            </tr>
        </thead>
        <tbody>   
            @foreach ($budget_items as $i => $item)
           
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $item->product_name }} {{ @$item->product? ' - ' . $item->product->code : ''}}</td>
                    <td>
                        <select name="unit[]" id="unit" class="custom-select unit">
                            <option value="{{ $item->unit }}">{{ $item->unit }}</option>
                        </select>
                    </td>
                    <td class="text-center">{{$item->product ? $item->product->code : ''}}</td>
                    <td class="text-center">{{ +$item->new_qty }}</td>
                    <td class="text-center">{{ +$item->issue_qty }}</td>
                    <td class="text-center">{{ (+$item->new_qty)-(+$item->issue_qty) }}</td>
                    <td>
                        @php
                            $qty_limit = 0;
                            $stock_qty = 0;
                            $product_id = '';
                        @endphp
                        <select name="warehouse_id[]" id="warehouse" class="custom-select wh">
                            @foreach ($stock as $stock_item)
                                @php
                                    $product = $item->product;
                                    if ($product && $product->parent_id == $stock_item->parent_id) {
                                        $qty_limit = $product->alert;
                                        $stock_qty = $stock_item->qty;
                                        $warehouse = $stock_item->warehouse;
                                        $product_id = $product->id;
                                    } else continue;
                                @endphp
                                <option value="{{ $warehouse->id }}"> 
                                    {{ $warehouse->title }} ({{ +$stock_qty }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="qty[]" id="qty" class="form-control qty"></td>
                    <td><input type="text" class="form-control qty_balance" data-id="{{$item->id}}" name="qty_balance[]" id="qty_balance"></td>
                    <td class="text-center"><input type="checkbox" class="status" name="" id="status-{{$i}}"></td>
                    <input type="hidden" name="budget_item_id[]" value="{{ $item->id }}">
                    <input type="hidden" name="product_id[]" value="{{ $product_id }}">
                    <input type="hidden" name="qty_limit[]" value="{{ +$qty_limit }}" class="qty-limit">
                    <input type="hidden" name="stock_qty[]" value="{{ +$stock_qty }}" class="qty-stock">
                    <input type="hidden" name="approved_qty[]" value="{{ +$item->new_qty }}" class="qty-approved">
                    {{-- Queued Requisition --}}
                    {{-- @if ($item->product)
                        
                        
                    @endif --}}
                    <input type="hidden" name="" class="qty_diff" value="{{+$item->new_qty - +$item->issue_qty }}" id="qty_diff">
                    <input type="hidden" name="system_name[]" id="system_name" value="{{$item->product ? $item->product->name : ''}}">
                    <input type="hidden" name="product_code[]" id="product_code" value="{{$item->product ? $item->product->code : ''}}">
                    <input type="hidden" name="item_name[]" class="item_name" value="{{$item->product_name}}" id="item_name-{{$i}}">
                    <input type="hidden" disabled name="uom[]" class="uom" value="{{$item->unit }}" id="uom-{{$i}}">
                    
                </tr>
            @endforeach
        </tbody>                
    </table>
</div>
<div class="row">  
    <div class="col-2 ml-auto">
        <label for="qty_total">Total Issue Qty</label>    
        {{ Form::text('qty_total', null, ['class' => 'form-control', 'id' => 'qty_total', 'readonly']) }}
        {{ Form::hidden('approved_qty_total', null, ['id' => 'approved_qty_total']) }}
    </div>                          
</div>
<div class="row mt-1"> 
                               
    <div class="col-2 ml-auto">  
        {{ Form::submit(@$projectstock? 'Update' : 'Issue Stock', ['class' =>'btn btn-primary btn-lg']) }}
    </div>
</div>


@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        datepicker: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {
        init() {
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#productsTbl').ready(this.tableReady);
            $('#productsTbl').on('keyup change', '.qty', this.tableEventChange);
            $('#productsTbl').on('keyup change', '.qty_balance', this.tableRequistionChange);
             $('.requisition').click(this.requisition);
                
        },

        tableReady() {
            $(this).find('tbody tr').each(function() {
                const el = $(this);
                const unit = el.find('.unit');
                const warehouse = el.find('.wh');
                const qty = el.find('.qty');
                if (!warehouse.children().length) {
                    [unit, warehouse, qty].forEach(el => el.attr('disabled', true));
                    const inputs = `<input type="hidden" name="unit[]">
                        <input type="hidden" name="warehouse_id[]"><input type="hidden" name="qty[]">`;
                    el.append(inputs);
                }
            });
        },

        requisition(e){
            var project = $('#proj').val();
            $('#project').val(project);
            $("#productsTbl tbody input:checked").each(function(){
            var $this = $(this);

            if($this.is(":checked")){
                swal({
                    title: 'Proceed to Stock Requisition?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                },()=>{
                    $('#exampleModal').modal('show');
                    $('#requisitionTbl tbody').html(" ");
                    var getselected = $('#productsTbl tbody input:checked').parents('tr');
                    var tr = getselected.clone().appendTo($('#requisitionTbl tbody').add());
                    tr.find('.qty_balance').prop('readonly', true);
                    tr.find('.qty').prop('readonly', true);
                });
               
            }else{
                swal({
                    title: 'Sorry Cannot Requisit?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }); 
            }
        });
          
        },

        tableEventChange(event) {
            const el = $(this);
            const qty = parseFloat(el.val());
            const row = el.parents('tr');
            const qtyLimit = parseFloat(row.find('.qty-limit').val());
            const qtyStock = parseFloat(row.find('.qty-stock').val());
            if (event.type == 'change') {
                if (qty > qtyStock) el.val(qtyStock).change();
                else if (qty == 0) el.val(1).change();
            }
            Form.qtyAlert(qty, qtyLimit, qtyStock);
            Form.columnTotals();
        },

        tableRequistionChange(event) {
            const el = $(this);
            const qty = parseFloat(el.val());
            const row = el.parents('tr');
            const qtyLimit = parseFloat(row.find('.qty-limit').val());
            const qtyDiff = parseFloat(row.find('.qty_diff').val());
            if (event.type == 'change') {
                if (qty > qtyDiff) el.val(qtyDiff).change();
                else if (qty == 0) el.val(1).change();
            }
            Form.qtyAlert(qty, qtyLimit, qtyDiff);
            Form.columnTotals();
        },

        qtyAlert(qty = 0, qtyLimit = 0, qtyStock = 0) {
            const msg = `<div class="alert alert-warning col-12 stock-alert" role="alert">
                <strong>Minimum inventory limit!</strong> Please restock product.</div>`;
            if (qtyStock <= qtyLimit && qty >= qtyStock) {
                $('.content-header div:first').before(msg);
                setTimeout(() => $('.content-header div:first').remove(), 4000);
                scroll(0,0);
            }
        },

        columnTotals() {
            let qtyTotal = 0;
            let qtyApprovedTotal = 0;
            $('#productsTbl tbody tr').each(function() {
                let qty = $(this).find('.qty').val();
                let qtyApproved = $(this).find('.qty-approved').val();
                if (qty > 0) {
                    qtyTotal += parseFloat(qty);
                    qtyApprovedTotal += parseFloat(qtyApproved);
                }
            });
            $('#qty_total').val(qtyTotal);
            $('#approved_qty_total').val(qtyTotal);
        },
    }

    $(() => Form.init());
</script>
@endsection
