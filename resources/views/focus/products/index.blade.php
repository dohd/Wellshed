@extends ('core.layouts.app')

@section ('title', trans('labels.backend.products.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ trans('labels.backend.products.management') }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.products.partials.products-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">                        
                        <div class="row form-group mb-2"> 
                            <div class="col-2">
                                <label for="itemType"><b>Product Type</b></label>
                                <select id="itemType" class="custom-select">
                                    <option value="">-- select type --</option>
                                    @foreach (['general', 'consumable', 'service', 'equipment','finished_goods','generic'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </div>   
                            <div class="col-2">
                                <label for="status"><b>Product Qty Status</b></label>
                                <select name="status" id="status" class="custom-select">
                                    @foreach (['in_stock', 'out_of_stock'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                    <option value="all">-- All --</option>
                                </select>
                            </div> 
                              
                            <div class="col-3">
                                <label for="warehouse">Product Location</label>
                                <select name="warehouse_id" id="warehouse" class="custom-select">
                                    <option value="">-- select location --</option>
                                    <option value="none">None</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                             
                        </div>
                        <div class="row form-group">
                            <div class="col-3">
                                <label for="category">Category</label>
                                <select name="category_id" id="category" class="custom-select" data-placeholder="Search Category">
                                    <option value=""></option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">
                                            @php
                                                $title = $category->title;
                                                $parent = @$category->parent_category->title;
                                                $child = @$category->child->title;
                                                echo implode(' || ', array_filter([$title, $parent, $child]));
                                            @endphp
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="sub_category">Sub Category</label>
                                <select name="sub_category_id" id="sub_category" class="custom-select" data-placeholder="Search Sub Category">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label for="sub_sub_category">Sub Sub Category</label>
                                <select name="sub_sub_category_id" id="sub_sub_category" class="custom-select" data-placeholder="Search Sub Category">
                                    <option value=""></option>
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
                            <div class="row">
                                <div class="col-6 h5"><b>Ending Inventory (FIFO):</b> &nbsp;<span class="ending-inventory">0.00</span></div>                           
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table id="productsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Item Code</th>
                                            <th>Category</th>
                                            <th>Sub Category</th>
                                            <th>Sub sub Category</th>
                                            <th>UoM</th>
                                            <th>Qty/Unit</th>
                                            @if (access()->allow('product-view_purchase_price'))
                                                <th>Purchase Price</th>
                                                <th>Fifo Cost</th>
                                            @endif
                                            <th>Re-order Qty</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
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
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
    };
    
    const Index = {
        loader: '',
        status: $('#status').val(),
        warehouseId: @json(request('warehouse_id')),
        categoryId: @json(request('productcategory_id')),
        itemType: $('#itemType').val(),

        init() {
            $('#category').select2({allowClear: true});
            $('#sub_category').select2({allowClear: true});
            $('#sub_sub_category').select2({allowClear: true});
            Index.loader = $('#productsTbl tbody tr:first').clone();
            Index.fetchEndingInventoryTotal();
            Index.drawDataTable();
            $('#status, #itemType').change(Index.statusChange);
            $('#warehouse').change(Index.warehouseChange);
            $('#category').change(Index.categoryChange);
            $('#sub_category').change(Index.subCategoryChange);
            $('#sub_sub_category').change(Index.subSubCategoryChange);
            if (Index.warehouseId) $('#warehouse').val(Index.warehouseId);
            if (Index.categoryId) $('#category').val(Index.categoryId);
        },

        subSubCategoryChange(){
            $('#productsTbl').DataTable().destroy();
            Index.drawDataTable();
        },
        subCategoryChange(){
            let sub_category_id = $(this).val();
            $.ajax({
                url: "{{ route('biller.products.get_categories')}}",
                method: "POST",
                data:{
                    type: 'sub_sub_category',
                    sub_cat_id: sub_category_id
                },
                success: function(data){
                    var select = $('#sub_sub_category');
                    select.empty();
                    select.append($('<option>', {
                        value: '',
                        text: 'Search Sub Sub Category'
                    }));

                    if (data.length === 0) {
                        select.append($('<option>', {
                            value: '',
                            text: 'No categories available'
                        }));
                    } else {
                        $.each(data, function(index, option) {
                            select.append($('<option>', { 
                                value: option.id,
                                text : option.title,
                                
                            }));
                        });
                        
                    }
                }
            });
            $('#productsTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        categoryChange() {
            Index.categoryId = $(this).val();
            let category_id = $(this).val();
            var select = $('#sub_category');
            select.empty();
            var select1 = $('#sub_sub_category');
            select1.empty();
            $.ajax({
                url: "{{ route('biller.products.get_categories')}}",
                method: "POST",
                data:{
                    type: 'sub_category',
                    cat_id: category_id
                },
                success: function(data){
                    
                    select.append($('<option>', {
                        value: '',
                        text: 'Search Sub Category'
                    }));

                    if (data.length === 0) {
                        select.append($('<option>', {
                            value: '',
                            text: 'No categories available'
                        }));
                    } else {
                        $.each(data, function(index, option) {
                            select.append($('<option>', { 
                                value: option.id,
                                text : option.title,
                                
                            }));
                        });
                    }
                }
            });
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        warehouseChange() {
            Index.warehouseId = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        statusChange() {
            Index.status = $(this).val();
            $('#productsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        fetchEndingInventoryTotal() {
            $('.ending-inventory').html('0.00');
            const url = "{{ route('biller.products.ending_inventory') }}";
            const params = {
                warehouse_id: Index.warehouseId,
                category_id: Index.categoryId,
            };
            $.post(url, params)
            .done(data => $('.ending-inventory').html(accounting.formatNumber(data.total)))
            .fail((xhr, status, error) => console.log(error));
        },

        drawDataTable() {
            $('#productsTbl tbody').html(`<tr>${Index.loader.html()}</tr>`);

            $.post("{{ route('biller.products.datatable_rows') }}", {
                warehouse_id: Index.warehouseId,
                category_id: Index.categoryId,
                sub_category_id: $('#sub_category').val(),
                sub_sub_category_id: $('#sub_sub_category').val(),
                status: Index.status,
                stock_type: Index.itemType,                
            })
            .done(data => {
                $('#productsTbl tbody').html('');
                if (!data.length) return;
                renderProducts(data);
            })
            .fail((xhr, status, error) => $('#productsTbl tbody').html(''));

            function renderProducts(products) {
                products.forEach((v, i) => {
                    const hasViewAccess = @json(access()->allow('manage-product'));
                    const hasEditAccess = @json(access()->allow('edit-product'));
                    const hasDeleteAccess = @json(access()->allow('delete-product'));
                    const csrfToken = @json(csrf_token());
                    const baseUrl = "{{ request()->url() }}/"
                    const viewUrl = baseUrl + v.id;
                    const editUrl = baseUrl + v.id + "/edit";
                    const deleteUrl = baseUrl + v.id;
                    
                    v.action_buttons = '';
                    if (hasViewAccess) v.action_buttons += `<a href="${viewUrl}" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i  class="fa fa-eye"></i></a>`;
                    if (hasEditAccess) v.action_buttons += `<a href="${editUrl}" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i  class="fa fa-pencil "></i></a>`;                        
                    if (hasDeleteAccess) v.action_buttons += `<a class="btn btn-danger round" data-method="delete" 
                        data-trans-button-cancel="cancel" data-trans-button-confirm="delete" data-trans-title="Are you sure" 
                        data-toggle="tooltip" data-placement="top" title="Delete" style="cursor:pointer;" onclick="$(this).find('form').submit();"
                        >
                            <i  class="fa fa-trash"></i>
                            <form action="${deleteUrl}" method="POST" name="delete_item" style="display:none;">
                                <input type="hidden" name="_method" value="delete">
                                <input type="hidden" name="_token" value="${csrfToken}">
                            </form>
                        </a>`;

                    let parent_cat = '';
                    let child_cat = '';
                    let grand_child_category = '';
                    if(v.grand_child_category){
                        grand_child_category = v.grand_child_category?.title;
                        child_cat = v.grand_child_category?.grand_children?.title;
                        parent_cat = v.grand_child_category?.parent?.title;
                    }
                    else if(v.child_cat){
                        grand_child_category = '';
                        child_cat = v.child_cat?.title;
                        parent_cat = v.child_cat?.parent?.title;
                    }
                    else if(v.parent_cat){
                        grand_child_category = '';
                        child_cat = '';
                        parent_cat = v.parent_cat?.title;
                    }
                    let name = v.name;
                    if (v.stock_type === 'service') {
                        name = 'SRVC - ' + name;
                    }
                    
                    const qty = v.variations.reduce((prev, curr) => prev + (+curr.qty), 0);
                    const moq = v.standard?.moq || '';
                    const row = `<tr>
                        <td>${i+1}</td>
                        <td>${name}</td>
                        <td>${v.standard?.code || 'Null-Code' }</td>
                        <!-- categories -->
                        <td>${parent_cat || ''}</td> 
                        <td>${child_cat || ''}</td>
                        <td>${grand_child_category || '' }</td>
                        <!-- end categories -->
                        <td>${v.unit?.code || 'Null-UoM' }</td>
                        <td class="qty">${qty}</td>                            
                        <td>${accounting.formatNumber(moq)}</td>
                        <td>${v.action_buttons}</td>
                    </tr>`
                    $('#productsTbl tbody').append(row);

                    const hasCostAccess = @json(access()->allow('product-view_purchase_price'));
                    if (hasCostAccess) {
                        const cost = v.standard?.purchase_price || '';
                        const fifo_cost = v.standard?.fifo_cost || '';
                        $('#productsTbl tbody tr:last td.qty').after(`<td>${accounting.formatNumber(cost)}</td><td>${accounting.formatNumber(fifo_cost)}</td>`);
                    }
                });

                $('#productsTbl').dataTable({
                    stateSave: true,
                    process: true,
                    responsive: true,
                    language: {@lang('datatable.strings')},
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, 'All']
                    ],
                    pageLength: 10,
                });
            }
        },
    };    

    $(Index.init);
</script>
@endsection
