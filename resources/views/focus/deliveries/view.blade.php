@extends ('core.layouts.app')

@section('title', 'View Delivery')

@section('page-header')
    <h1>
        <small>View Delivery</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Delivery</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.deliveries.partials.deliveries-header-buttons')
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
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>#Order No.</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ gen4tid('ORD-', $delivery->order['tid']) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Customer</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ optional(optional($delivery->order)->customer)->company ?? '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Delivery Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ dateFormat($delivery->date) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Expected Delivery Time</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ timeFormat($delivery->delivery_schedule->delivery_time) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ ucfirst($delivery->status) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Remarks</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $delivery->status_note }}</p>
                                        </div>
                                    </div>


                                </div>
                                <div class="card-body">
                                    <table id="itemsTbl" class="table table-bordered" width="100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 45%;">Product</th>
                                                
                                                <th style="width: 20%;">Planned Qty</th>
                                                <th style="width: 10%;">Returned Qty</th>
                                       
                                                <th style="width: 15%;">Remaining Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (count($delivery->items) > 0)
                                                @foreach ($delivery->items as $index => $item)
                                                    <tr>
                                                        <td>
                                                            {{ $item->product->name }}
                                                        </td>
                                                        
                                                        <td>
                                                            {{ $item->planned_qty }}
                                                        </td>
                                                        <td>
                                                            {{ numberFormat($item->returned_qty) }}
                                                        </td>
                        
                                                        <td><span class="amt">{{ numberFormat($item->remaining_qty) }}</span></td>
                                                    </tr>
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
