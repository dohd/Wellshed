@extends ('core.layouts.app')

@section ('title', 'View Selling Price Costing')

@section('page-header')
    <h1>
        <small>View Selling Price Costing</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Selling Price Costing</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.sell_prices.partials.sell_prices-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            @php
                                $not_linked_items = count($sell_price->items()->where('product_id','=',0)->get());
                            @endphp
                            <div class="card-header">
                                @permission('sell_prices_approval')  
                                    @if ($not_linked_items == 0 && $sell_price->status != 'approved')
                                        
                                    <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                                        <i class="fa fa-pencil" aria-hidden="true"></i> status
                                    </a>
                                    @endif
                                @endauth
                               
                            </div>

                            <div class="card-content">

                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Import Request</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            @php
                                                $name = '';
                                                if($sell_price->import_request){
                                                    $name = gen4tid('IMP-',$sell_price->import_request['tid']) .'-' . $sell_price->import_request['notes'];
                                                }
                                            @endphp
                                            <p>{{$name}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Type</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ucfirst($sell_price['type'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Minumum Selling Price Percentage / Fixed</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($sell_price['percent_fixed_value'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Recommended Price Type</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ucfirst($sell_price['recommend_type'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Recommended Selling Price Percentage / Fixed</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($sell_price['recommended_value'])}}</p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ucfirst($sell_price['status'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Remark</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$sell_price['status_note']}}</p>
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive mt-2">
                        <table class="table table-stripped" id="productTbl" cellspan="2">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>UoM</th>
                                    <th>Inventory Link</th>
                                    <th>Landed Price</th>
                                    <th>Minimum Selling Price</th>
                                    <th>Recommended Selling Price</th>
                                    <th>Minimum Order Qty (MoQ)</th>
                                    <th>Reorder Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                               @isset($sell_price)
                                   @foreach ($sell_price->items as $i => $item)
                                   <tr>
                                    <td>{{$i+1}}</td>
                                    <td>{{$item->import_req_item ? $item->import_req_item->product_name : ''}}</td>
                                    <td>{{$item->import_req_item ? +$item->import_req_item->qty : ''}}</td>
                                    <td>{{$item->import_req_item ? $item->import_req_item->unit : ''}}</td>
                                    <td class="{{$item->product_id ? 'text-success' : 'text-danger'}}">{{$item->product_id ? 'Linked' : 'Not Linked'}}</td>
                                    <td>{{numberFormat($item->landed_price)}}</td>
                                    <td>{{numberFormat($item->minimum_selling_price)}}</td>
                                    <td>{{numberFormat($item->recommended_selling_price)}}</td>
                                    <td>{{numberFormat($item->moq)}}</td>
                                    <td>{{numberFormat($item->reorder_level)}}</td>
                                    <td>
                                        @if ($item->product_id)
                                        <a href="{{route('biller.sell_prices.update_prices', $item->id)}}" onclick="return confirm('Are you Sure to Update Prices!');" class="btn btn-sm btn-secondary">Update Prices</a>
                                        @else
                                        <a href="{{route('biller.sell_prices.create_product', $item->id)}}" target="_blank" onclick="return confirm('Are you Sure to Create Product!');" class="btn btn-sm btn-info">Create Product</a>
                                        <a href="#" class="btn btn-warning select_product btn-sm mr-1" data-toggle="modal" data-target="#productModal">
                                            <i class="fa fa-pencil" aria-hidden="true"></i> Select
                                        </a>
                                        @endif
                                    </td>
                                    <input type="hidden" name="product_id[]" value="{{$item->product_id}}">
                                    <input type="hidden" name="import_request_item_id[]" value="{{$item->import_request_item_id}}">
                                    <input type="hidden" name="id[]" value="{{$item->id}}" class="id">
                                </tr>
                                   @endforeach
                               @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.sell_prices.partials.product_modal')
        @include('focus.sell_prices.partials.status')
    </div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {};
        const Index = {
            init(){
                $('#product').select2({allowClear: true});
                $('#productTbl').on('click', '.select_product', Index.selectProduct);
            },

            selectProduct(){
                const el = $(this);
                const row = el.parents('tr:first');
                let id = row.find('.id').val();
                $('#item_id').val(id);
            }
        };
        $(()=>Index.init());
    </script>
@endsection