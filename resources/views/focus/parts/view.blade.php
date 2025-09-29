@extends ('core.layouts.app')

@section ('title', 'View Parts')

@section('page-header')
    <h1>
        <small>View Parts</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Parts</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.parts.partials.parts-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">
                                <div class="card-header">
                                    <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#addpartModal">
                                        <i class="fa fa-pencil" aria-hidden="true"></i> Add Product
                                    </a>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Name</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$part['name']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Description</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$part['description']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Finished Good</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{@$part->productvar['name']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Standard Template</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{@$part->template['name']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Total Quantity</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($part['total_qty'])}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table id="partsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Unit</th>
                                    <th>Product Code</th>
                                    <th>Qty</th>
                                    <th>Available Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($part)
                                    @foreach ($part->part_items as $k => $item)
                                        <tr>
                                            <td><span class="numbering">{{$k+1}}</span></td>
                                            <td>{{@$item->product->name}}</td>
                                            <td>{{ @$item->unit->code }}
                                            </td> 
                                            <td><span class="code" id="code-p{{$k}}">{{@$item->product->code}}</span></td>
                                            <td>{{numberFormat($item->qty)}}</td>
                                            <td 
                                                @if (@$item->product->qty > $item->qty) 
                                                    style="color: green;" 
                                                @else 
                                                    style="color: red;" 
                                                @endif
                                            >
                                                {{numberFormat(@$item->product->qty)}}
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.parts.partials.add_part_to_finished')
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {

        init() {
            $.ajaxSetup(config.ajax);
            $('#user').select2({allowClear: true});
            $('#product').select2({allowClear: true});

           
        },

       
    };

    $(() => Form.init());
</script>
@endsection