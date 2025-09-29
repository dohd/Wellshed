<div class="form-group row">
    <div class="col-4">
        <label for="import_request">Search Import Request</label>
        <select name="import_request_id" id="import_request" data-placeholder="Search Import Request" class="form-control">
            <option value="">Search Import Rquest</option>
            @foreach ($import_requests as $item)
                <option value="{{$item->id}}" {{$item->id == @$sell_price->import_request_id ? 'selected' : ''}}>{{gen4tid('IMP-', $item->tid) . '-'.$item->notes}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="type">Type</label>
        <select name="type" id="type" class="form-control">
            <option value="">--select type--</option>
            @foreach (['percentage','fixed'] as $item)
                <option value="{{$item}}" {{$item == @$sell_price->type ? 'selected' : ''}}>{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="value">Minumum Selling Price Percentage / Fixed</label>
        <input type="text" value="{{numberFormat(@$sell_price->percent_fixed_value)}}" name="percent_fixed_value" id="percent_fixed_value" class="form-control">
    </div>
    <div class="col-2">
        <label for="type">Recommended Price Type</label>
        <select name="recommend_type" id="recommend_type" class="form-control">
            <option value="">--select recommend_type--</option>
            @foreach (['percentage','fixed'] as $item)
                <option value="{{$item}}" {{$item == @$sell_price->recommend_type ? 'selected' : ''}}>{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="value">Recommended Selling Price Percentage / Fixed</label>
        <input type="text" value="{{numberFormat(@$sell_price->recommended_value)}}" name="recommended_value" id="recommended_value" class="form-control">
    </div>
</div>
<div class="form-group">
    @include('focus.sell_prices.partials.sell_price_items')
</div>