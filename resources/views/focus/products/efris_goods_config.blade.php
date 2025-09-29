@extends ('core.layouts.app')
@section ('title', 'EFRIS Commodity Assigning')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-6">
            <h4 class="content-header-title pt-1">EFRIS Commodity Assigning</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    <div class="btn-group">
                        <a href="{{ route('biller.products.efris_goods_upload_view') }}" class="btn btn-info  btn-lighten-2">
                            <i class="fa fa-cloud-upload"></i> EFRIS Goods Upload
                        </a>
                        <a href="{{ route('biller.products.index') }}" class="btn btn-danger  btn-lighten-2">
                            <i class="fa fa-list-alt"></i> List
                        </a>
                    </div>
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
                            <div class="row">
                                <!-- Goods/Service List -->
                                <div class="col-md-4">
                                    <style>
                                        .tree-node {
                                          cursor: pointer;
                                        }
                                        .nested {
                                          display: none;
                                          margin-left: 20px;
                                        }
                                        .active {
                                          display: block;
                                        }
                                    </style>
                                    <div>
                                        <!-- Commodity Search -->
                                        <div class="form-inline">
                                            <div class="input-group mr-sm-2">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></div>
                                                </div>
                                                <input type="text" class="form-control" id="searchGoodsCode" placeholder="Commodity Name or Code" style="width: 26em;">
                                            </div>
                                        </div>
                                        <!-- Commodity List -->
                                        <div class="mt-1 commodity-list-ctn" style="max-height: 700px; overflow-y: auto">
                                            <ul class="list-unstyled">
                                              @foreach ($efrisGoodsCategories as $item)
                                                <li class="mb-1">
                                                    <span class="tree-node level-0" min_family_code="{{ $item->min_family_code }}" max_family_code="{{ $item->max_family_code }}">▶ ({{ $item->min_family_code }}) {{ $item->segment_name }}</span>
                                                </li>
                                              @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product List -->
                                <div class="col-md-8">
                                    <div class="row mb-1">
                                        <div class="col-md-4 pt-1">
                                            <h5><b>Commodity Selected: </b><span id="commoditySel">None</span></h5>
                                        </div>
                                        <div class="col-md-4 pt-1">
                                            <h5><b>No. Products Assigned: </b><span id="productSel">0</span></h5>
                                        </div>
                                        <div class="col-md-4">
                                            <button id="assignBtn" class="btn btn-success " disabled>Assign Selected</button>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    <div class="row">                          
                                        <div class="col-4">
                                            <label for="category">Product Category (W/O Commodity Code)</label>
                                            <select id="category" class="custom-select" data-placeholder="Search Category">
                                                @foreach ($productCategories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->title }} ({{ $category->product_variations_count }})</option>
                                                @endforeach
                                            </select>
                                        </div>  
                                        <div class="col-3">
                                            <label for="warehouse">Product Location</label>
                                            <select id="warehouse" class="custom-select">
                                                <option value="">-- select location --</option>
                                                <option value="none">None</option>
                                                @foreach ($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <hr>
                                    
                                    <div class="table-responsive">
                                        <table id="productsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="checkAll"></th> 
                                                    <th>Item Code</th>
                                                    <th>Item Name</th>
                                                    <th>Category</th>
                                                    <th>Assigned Code</th>
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
        {{ Form::open(['route' => 'biller.products.efris_assign_commodity_code', 'method' => 'POST']) }}
            <input type="hidden" name="commodity_code" id="commodityCode">
            <input type="hidden" name="productvar_ids" id="productvarIds">
        {{ Form::close() }}
    </div>
</div>
@endsection

@section('after-scripts')
    @include('focus.products.efris_goods_config_js')
@endsection
