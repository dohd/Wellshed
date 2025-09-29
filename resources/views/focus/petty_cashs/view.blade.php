@extends ('core.layouts.app')

@section ('title', 'View Petty Cash')

@section('page-header')
    <h1>
        <small>View Petty Cash</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Petty Cash</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.petty_cashs.partials.petty_cashs-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                                    <i class="fa fa-pencil" aria-hidden="true"></i> Status
                                </a>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Title</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$petty_cash['title']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{dateFormat($petty_cash['date'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Expected Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{dateFormat($petty_cash['expected_date'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Description</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$petty_cash['description']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Item Type</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ ucfirst($petty_cash['item_type'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>{{$petty_cash->item_type == 'purchase_requisition' ? 'Purchase Requisition (PR)' : ''}}</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                        @if ($petty_cash['item_type'] == 'purchase_requisition')
                                            <p>{{gen4tid('PR-',@$petty_cash->pr['tid'])}} - {{@$petty_cash->pr['note']}}</p>
                                        @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>User Type</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ ucfirst($petty_cash['user_type'])}}</p>
                                        </div>
                                    </div>
                                     
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            @if ($petty_cash['user_type'] == 'employee')  
                                                <p>{{ucfirst($petty_cash['user_type'])}}</p>
                                            @elseif($petty_cash['user_type'] == 'casual')
                                                <p>{{ucfirst($petty_cash['user_type'])}}</p>
                                            @else
                                                <p>{{ucfirst($petty_cash['user_type'])}}</p>
                                            @endif
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            @if ($petty_cash['user_type'] == 'employee')
                                                <p>{{@$petty_cash->employee['fullname']}}</p>
                                            @elseif($petty_cash['user_type'] == 'casual')
                                                <p>{{@$petty_cash->casual_labourer['name']}}</p>
                                            @else
                                            <p>{{@$petty_cash->third_party_user['name']}}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>VAT</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($petty_cash['tax'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>SubTotal</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($petty_cash['subtotal'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Tax</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($petty_cash['tax_amount'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Total</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($petty_cash['total'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Given Amount</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($petty_cash['amount_given'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Balance</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($petty_cash['balance'])}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="stockTbl" class="table" widht="50%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>UoM</th>
                                        <th>Qty</th>
                                        <th>Vat</th>
                                        <th>Price</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($petty_cash)
                                        @foreach ($petty_cash->items as $i => $item)
                                            <tr>
                                                <td>{{$i+1}}</td>
                                                <td>{{$item->product_name}}</td>
                                                <td>{{$item->uom}}</td>
                                                <td>{{$item->qty}}</td>
                                                <td>{{$item->tax}}</td>
                                                <td>{{$item->price}}</td>
                                                <td>{{$item->amount}}</td>
                                            </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <legend>Approval Status</legend>
                        <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>Status Note</th>
                                    <th>Approval Date</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($petty_cash->approvers) > 0)
                                    @foreach ($petty_cash->approvers as $i => $item)
                                        <tr>
                                            <td>{{$i+1}}</td>
                                            <td>{{ucfirst($item->status)}}</td>
                                            <td>{{$item->status_note}}</td>
                                            <td>{{$item->date}}</td>
                                            <td>{{$item->approved_user ? $item->approved_user->fullname : ''}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @include('focus.petty_cashs.partials.status')
            </div>
        </div>
    </div>
@endsection
