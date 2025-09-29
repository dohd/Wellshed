
    <div class='form-group row'>
        <div class='col-4'>
            {{ Form::label( 'item_name', 'Item Name',['class' => 'control-label']) }}
           {{ Form::text('item_name', null, ['class' => 'form-control round', 'readonly' ]) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'qty_balance', 'Requested Quantity',['class' => 'control-label']) }}
            {{ Form::number('qty_balance', null, ['class' => 'form-control round', 'id'=>'qty-balance']) }}
            <input type="hidden" value="{{$queuerequisition->qty_balance}}" id="qty">
        </div>
        <div class='col-4'>
            {{ Form::label( 'uom', 'UOM',['class' => 'control-label']) }}
            {{ Form::text('uom', null, ['class' => 'form-control round', 'readonly']) }}
        </div>
        <input type="hidden" name="" id="approval-balance" value="{{@$item}}">
        <input type="hidden" name="budget_item_id" value="{{$queuerequisition->budget_item_id}}" id="">
    </div>
    <div class='form-group row'>
        <div class='col-4'>
            {{ Form::label( 'client_branch', 'Client Branch',['class' => 'control-label']) }}
            {{ Form::text('client_branch', null, ['class' => 'form-control round', 'readonly']) }}
        </div>
        <div class='col-4'>
            {{ Form::label( 'system_name', 'System Description',['class' => 'control-label']) }}
            {{ Form::text('system_name', null, ['class' => 'form-control round', 'readonly']) }}
        </div>
        <div class="col-4">
            {{ Form::label( 'product_code', 'Product Code',['class' => 'control-label']) }}
            {{ Form::text('product_code', null, ['class' => 'form-control round', 'readonly']) }}
        </div>
    </div>
    <div class="form-group row">
        <div class="col-4">
            {{ Form::label( 'item_qty', 'Inventory Quantity',['class' => 'control-label']) }}
            {{ Form::text('item_qty', null, ['class' => 'form-control round', 'placeholder' => '', 'readonly']) }}
        </div>
    </div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
        $('#qty-balance').on('keyup change', quantity);
        function quantity (e) {
            var el = $(this);
            var qty = $('#approval-balance').val();
           if (qty) {
                var qty_balance = el.val();
                if (e.type == 'change') {
            
                    if (qty_balance > qty) {
                        el.val(qty).change();
                    }
                    //el.val(qty_balance).change();
                }
           }
        }

    </script>
@endsection
