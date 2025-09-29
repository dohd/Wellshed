@extends ('core.layouts.app')

@section ('title', 'Create | Price List Creation')

@section('content')
<div>
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Price List Creation</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.pricelistsSupplier.partials.pricelists-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                                <div class="row form-group">
                                    <div class="col-3">
                                        <label for="products">Search Product</label>
                                        <select name="product_id" id="product" class="form-control" data-placeholder="Search Product">
                                            <option value="">Search Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{$product->id}}">{{$product->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <label for="category" class="h4">Product Category</label>
                                        <select name="category_id" id="category" class="custom-select">
                                            <option value="">-- select category --</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>  
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
                                <table id="productsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('labels.backend.products.table.id') }}</th>
                                            <th>Description</th>
                                            <th>Category Name</th>
                                            <th>Product Code</th>
                                            <th>Unit (Qty)</th>
                                            <th>Unit Code</th>
                                            <th>Purchase Price</th>
                                            {{-- <th>{{ trans('general.createdat') }}</th>
                                            <th>{{ trans('labels.general.actions') }}</th> --}}
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
    @include('focus.pricelistsSupplier.partials.add-supplier-list')
    </div>
</div>

@endsection


@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };
    
    const Index = {
        status: '',
        warehouseId: @json(request('warehouse_id')),
        productId: @json(request('product_id')),
        categoryId: @json(request('productcategory_id')),
        supplierProduct: @json(@$supplier_product),

        init() {
            this.drawDataTable();
            $('#product').select2({allowClear: true});
            $('#status').change(this.statusChange);
            $('#warehouse').val(this.warehouseId).change(this.warehouseChange);
            $('#category').val(this.categoryId).change(this.categoryChange);
            $('#product').val(this.productId).change(this.productChange);
            $('#productsTbl').on('click', '.click', function (e) {
                var data = e.target.getAttribute('product_code');
                $('#code').val(data);
                var description = e.target.getAttribute('des');
                $('#description').val(description);
                var uom = e.target.getAttribute('uom');
                $('#uom').val(uom);
                var product_id = e.target.getAttribute('item_id');
                $('#product_id').val(product_id);
            });
            
            if ($('#contract').val()) {
                      $('#supplier').select2({allowClear: true}).attr('disabled', true);
                      $('#contract').attr('readonly', true);
                  }
                  $('#supplier').select2({allowClear: true});
                  $('#rate').focusout(this.rateChange);
      
                  if (this.supplierProduct) {
                      $('#rate').trigger('focusout');
                  } else {
                      $('#supplier').val('').trigger('change');
                  }
        },
        rateChange() {
                  const value = accounting.unformat($(this).val());
                  $(this).val(accounting.formatNumber(value));
              },

        categoryChange() {
            Index.categoryId = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        warehouseChange() {
            Index.warehouseId = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },
        productChange() {
            Index.productId = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        statusChange() {
            Index.status = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        drawDataTable() {
            $('#productsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.pricelistsSupplier.gets") }}',
                    type: 'post',
                    data: {
                        warehouse_id: this.warehouseId,
                        category_id: this.categoryId,
                        product_id: this.productId,
                        status: this.status
                    }
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'productcategory_id', name: 'productcategory_id'},
                    {data: 'code', name: 'code'},                    
                    {data: 'qty', name: 'qty'},
                    {data: 'unit', name: 'unit'},
                    {data: 'price', name: 'price'},
                    // {data: 'created_at', name: 'created_at'},
                    // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
        },
    };    

    $(() => Index.init());
</script>
@endsection
