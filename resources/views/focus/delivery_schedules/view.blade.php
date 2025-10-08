@extends ('core.layouts.app')

@section('title', 'View Delivery Schedule')

@section('page-header')
    <h1>
        <small>View Delivery Schedule</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Delivery Schedule</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.delivery_schedules.partials.delivery_schedules-header-buttons')
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
                                            <p>{{ gen4tid('ORD-', $delivery_schedule->order['tid']) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Customer</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ optional(optional($delivery_schedule->order)->customer)->company ?? '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Delivery Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ dateFormat($delivery_schedule->delivery_date) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Expected Delivery Time</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ timeFormat($delivery_schedule->delivery_time) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $delivery_schedule->status }}</p>
                                        </div>
                                    </div>


                                </div>
                                <div class="card-body">
                                    <table id="itemsTbl" class="table table-bordered" width="100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 45%;">Product</th>
                                                
                                                <th style="width: 20%;">Quantity</th>
                                                <th style="width: 10%;">Rate</th>
                                       
                                                <th style="width: 15%;">Amount</th>
                                                <th style="width: 10%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- {{ dd($delivery_schedule) }} --}}
                                            @foreach ($delivery_schedule->items as $index => $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->product->name }}
                                                    </td>
                                                    
                                                    <td>
                                                        {{ $item->qty }}
                                                    </td>
                                                    <td>
                                                        {{ numberFormat($item->rate) }}
                                                    </td>
                    
                                                    <td><span class="amt">{{ numberFormat($item->amount) }}</span></td>
                                                    <td>
                                                        <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                                            <i class="fa fa-trash fa-lg text-danger"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
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
