@extends ('core.layouts.app')

@section('title', 'View Order')

@section('page-header')
    <h1>
        <small>View Order</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Order</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customer_orders.partials.customer_orders-header-buttons')
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
                                            <p>Order No</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ gen4tid('ORD-', $orders['tid']) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Customer</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $orders->customer['company'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Order Type</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $orders['order_type'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Total</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ numberFormat($orders['total']) }}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <table id="budgetsTbl" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Schedule No.</th>
                                            <th>Delivery Day</th>
                                            <th>Delivery Date</th>
                                            <th>Status</th>
                                            <th>Planned Item Count</th>
                                            <th>Delivered Item Count</th>
                                            <th>Returned Item Count</th>
                                            {{-- <th>Actions</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders->schedules as $i => $item)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ gen4tid('DS-',$item->tid) }}</td>
                                                <td>{{ $item->delivery_frequency->delivery_days ?? '' }}</td>
                                                <td>{{ $item->delivery_date }}</td>
                                                <td>{{ $item->status }}</td>
                                                <td>{{ count($item->items) ?? 0 }}</td>
                                                <td>{{ 0 }}</td>
                                                <td>{{ 0 }}</td>
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
@endsection
