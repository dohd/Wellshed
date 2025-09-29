@if ($elem == 'dropdown')
  <div class="dropdown" style="margin-top: 3px;">
    <button class="btn dropdown-toggle btn-sm bg-gradient-directional-blue white" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
      <a class="dropdown-item add-product" href="javascript:void(0)"><i class="fa fa-plus" aria-hidden="true"></i> Product</a>
      <a class="dropdown-item add-title" href="javascript:void(0)"><i class="fa fa-plus" aria-hidden="true"></i> Title</a>
      <a class="dropdown-item del-row" href="javascript:void(0)"><i class="fa fa-trash text-danger" aria-hidden="true"></i> Delete</a>
    </div>
  </div>
@endif

@if ($elem == 'vat')
	<div class="row no-gutters">
    <div class="col-5">
      <select class="custom-select tax-rate" name="tax_rate[]">
        <option value="">--VAT--</option>
        @foreach ($additionals as $item)
            <option value="{{ +$item->value }}">{{ $item->value == 0 ? 'OFF' : +$item->value . '%' }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-7">
      <input type="text" class="form-control tax" name="product_tax[]" readonly>
    </div>
	</div>
@endif