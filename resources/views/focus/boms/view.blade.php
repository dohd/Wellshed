@extends ('core.layouts.app')

@section ('title', 'View BoM / MTO')

@section('page-header')
    <h1>
        Manage BoM
        <small>View BoM / MTO</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View BoM / MTO</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.boms.partials.boms-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="btn-group float-right">
                                    <a href="{{ route('biller.boms.edit', $bom) }}" class="btn btn-blue btn-outline-accent-5 btn-sm">
                                        <i class="fa fa-pencil"></i> {{trans('buttons.general.crud.edit')}}
                                    </a>&nbsp;
                                    {{ Form::open(['route' => ['biller.boms.destroy', $bom], 'method' => 'DELETE']) }}
                                    <button type="submit" class="btn btn-danger btn-outline-accent-5 btn-sm" id="delbom">
                                        <i class="fa fa-trash"></i> {{ trans('buttons.general.crud.delete') }}
                                    </button>
                                    {{ Form::close() }}
                                </div>
                            </div>

                            <div class="card-content">

                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Title</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$bom['name']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @php
                                            $customer_name = '';

                                            if (!empty(@$bom->lead->customer)) {
                                                $customer_name = @$bom->lead->customer->company ?? '';

                                                if (!empty(@$bom->lead->branch)) {
                                                    $customer_name .= ' - ' . (@$bom->lead->branch->name ?? '');
                                                }
                                            } else {
                                                $customer_name = @$bom->lead->client_name ?? '';
                                            }

                                            // create mode
                                            $prefix = 'TKT';
                                        @endphp
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Ticket</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ gen4tid("{$prefix}-", @$bom->lead->reference) }} - {{ $customer_name }} - {{ @$bom->lead->title }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Total BoQ Amount</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat(@$bom->boq['total_boq_amount'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Created At</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$bom['created_at']}}</p>
                                        </div>
                                    </div>


                                </div>

                                <div class="card-body">
                                    <table id="bomTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                            <tr class="bg-gradient-directional-blue white">
                                                <th>#No</th>
                                                <th>Product</th>
                                                <th>UoM</th>
                                                <th>Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                    
                                            <!-- Edit Quote or PI -->
                                            @if (isset($bom))
                                                @foreach ($bom->items as $k => $item)
                                                    @if ($item->type == "product")
                                                        <!-- Product Row -->
                                                        <tr class="">
                                                            @php
                                                                
                                                            @endphp
                                                            <td>{{$item->numbering}}</td>
                                                                         
                                                            <td>
                                                               {{@$item->product->name}}
                                                            </td>
                                                            <td>
                                                               {{@$item->unit_id}}
                                                            </td>
                                
                                                            <td>{{number_format($item->qty, 2)}}</td>
                                                            
                                                        </tr>
                                                    @else
                                                        <!-- Title Row  -->
                                                        <tr>
                                                            <td>{{$item->numbering}}</td>
                                                            <td colspan="3">
                                                                <b>{{$item->product_name}}</b>
                                                            </td>
                                                         
                                                        </tr>
                                        
                                                    @endif
                                                @endforeach
                                            @endif        
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
