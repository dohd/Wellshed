@extends ('core.layouts.app')
@section ('title', 'Products Opening Stock')

@section('content')
<div class="content-wrapper">
    <div class="content-body "> 
        {{ Form::open(['route' => 'biller.settings.opening_stock', 'method' => 'POST', 'id' => 'openingstock-form']) }}
            <div class="card">
                <div class="card-header border-bottom-blue-grey">
                    <h4 class="card-title">Products Opening Stock</h4>
                </div>
                <div class="card-content mb-0 pb-0">
                    <div class="card-body">
                        <p class="font-weight-bold font-italic h4 ml-1 mb-2">
                            <span class="text-danger">***</span> Enter Quantity, Unit Cost: for All Products and Save At Once
                        </p>
                        <div class='row'>
                            <div class="col-md-3">
                                {{ Form::label('date', 'As of Date', ['class' => 'col-12 control-label']) }}
                                <div class='col pr-0'>
                                    {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-9">
                                {{ Form::label('note', 'Note', ['class' => 'col-12 control-label']) }}
                                <div class='col'>
                                    {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        
            
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="row mb-1">
                            <div class="col-md-4">
                                <div class="col pr-0">
                                    <select id="warehouse" class="form-control custom-select" autocomplete="off">
                                        <option value="">-- Filter Products To Display By Location --</option>
                                        @foreach ($warehouses as $item)
                                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive mb-2" style="height: 80vh">
                            <table id="productsTbl" class="table table-sm tfr my_stripe_single text-center">
                                <thead>
                                    <tr class="bg-gradient-directional-blue white">
                                        <th>#</th>
                                        <th>Location</th>
                                        <th>Stock Item</th>
                                        <th>Product Code</th>
                                        <th width="10%">Unit</th>
                                        <th width="14%">Quantity</th>
                                        <th width="16%">Unit Cost</th>
                                        <th width="18%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product_vars as $i => $item)                   
                                        <tr>
                                            <td><span class="index">{{ $i+1 }}</span></td>
                                            <td><span class="location">{{ @$item->warehouse->title }}</span></td>
                                            <td><span class="name float-left">{{ $item->name }}</span></td>                    
                                            <td><span>{{ $item->code }}</span></td>                    
                                            <td><span class="unit">{{ @$item->product->unit->code }}</span></td>
                                            @if ($item->openingstock_item)
                                                <td><input type="text" name="qty[]" value="{{ +$item->openingstock_item->qty }}" class="form-control qty"></td>
                                                <td><input type="text" name="cost[]" value="{{ numberFormat($item->openingstock_item->cost) }}" class="form-control cost"></td>
                                                <td><input type="text"  name="amount[]" value="{{ numberFormat($item->openingstock_item->qty * $item->openingstock_item->cost) }}" class="form-control amount" readonly></td>
                                            @else
                                                <td><input type="text" name="qty[]" value="0" class="form-control qty"></td>
                                                <td><input type="text" name="cost[]" value="0" class="form-control cost"></td>
                                                <td><input type="text"  name="amount[]" value="0" class="form-control amount" readonly></td>
                                            @endif
                                            <input type="hidden" name="productvar_id[]" value="{{ $item->id }}" class="prodvar-id">
                                            <input type="hidden" name="product_id[]" value="{{ $item->parent_id }}" class="prod-id">
                                            <input type="hidden" name="warehouse_id[]" value="{{ @$item->warehouse->id ?: 0 }}" class="location-id">
                                        </tr>
                                    @endforeach       
                                </tbody>
                            </table>
                        </div>
                        <table class="tfr mb-2" id="locationsTbl">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th width="70%">Location</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($warehouses as $i => $item)
                                    <tr>
                                        <td class="pb-0"><span>{{ $item->title }}</span></td>
                                        <td class="pb-0 font-weight-bold"><span class="wh-total">0.00</span></td>
                                        <input type="hidden" value="{{ $item->id }}" class="wh-id">
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="form-group row">
                            <div class="col-2 ml-auto">
                                <label for="total" class="mb-0">Total Amount</label>
                                {{ Form::text('total', null, ['class' => 'form-control', 'id' => 'total','readonly']) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 ml-auto">
                                {{ Form::button('Save', ['class' => 'btn btn-primary btn-lg float-right reset', 'type' => 'submit']) }}
                                {{ Form::button('Reset', ['class' => 'btn btn-danger btn-lg float-right mr-1', 'id' => 'reset']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {{ Form::close() }}
    </div>
</div>
@endsection

@section('extra-scripts')
<script type="text/javascript">
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };
    // ajax success callback
    function trigger(data) {return data;}
    
    const Index = {
        init() {
            $('#productsTbl tbody input').css({height: '30px'})

            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#productsTbl').on('keyup', '.qty, .cost', Index.qtyCostKeyUp);
            $('#warehouse').change(Index.warehouseChange);
            $('#reset').click(Index.resetForm);
            $('#openingstock-form').submit(Index.formSubmit);
            
            const openingStock = @json(@$openingstock);
            if (openingStock && openingStock.id) {
                $('#date').datepicker('setDate', new Date(openingStock.date));
                $('#note').val(openingStock.note);
                Index.calcTotals();
            }
        },

        formSubmit(e) {
            e.preventDefault();
            if (!$('#date').val()) return;
            const data = {};
            const formData = new FormData($(this)[0]); 
            for (const [key, value] of formData) {
                let field;
                let isArray = false;
                if (key.includes('[]')) {
                    field = key.replace('[]', '');
                    if (!data[field]) data[field] = '';
                    isArray = true;
                } else field = key;
                if (isArray && data[field]) data[field] = data[field] + ';' + value;
                else data[field] = value;
            }
            return addObject({form: data, url: $(this).attr('action')}, true);
        },

        resetForm() {
            if (confirm('Are you sure to erase all records?')) {
                $('#note').val('');
                $('#total').val('');
                $('#productsTbl').find('input').val('');
                setTimeout(() => $('#openingstock-form').submit(), 500);
            }
        },

        warehouseChange() {
            const value = $(this).val();
            if (!value) return $('#productsTbl tbody tr').css('display', '');
            $('#productsTbl tbody tr').each(function() {
                const locationId = $(this).find('.location-id').val();
                if (locationId == value) $(this).css('display', '');
                else $(this).css('display', 'none');
            });
        },

        qtyCostKeyUp() {
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const qty = accounting.unformat(row.find('.qty').val());
            const amount = qty * cost;
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },

        calcTotals() {
            let grandtotal = 0;
            let locationTotals = {};
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').val());
                grandtotal += amount;
                const locationId = $(this).find('.location-id').val();
                if (locationTotals[locationId]) locationTotals[locationId] += amount;
                else locationTotals[locationId] = amount;
            });
            $('#locationsTbl tbody tr').each(function() {
                const locationId = $(this).find('.wh-id').val();
                const amount = accounting.unformat(locationTotals[locationId]);
                $(this).find('.wh-total').text(accounting.formatNumber(amount));
            });
            $('#total').val(accounting.formatNumber(grandtotal));
        },
    };

    $(Index.init);
</script>
@endsection
