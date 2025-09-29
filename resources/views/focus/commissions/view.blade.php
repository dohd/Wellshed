@extends ('core.layouts.app')

@section ('title', 'View Commission')

@section('page-header')
    <h1>
        <small>View Commission Payment</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Commission Payment</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.commissions.partials.commissions-header-buttons')
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
                                            <p>Commission Payment No.</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{gen4tid('CM-',$commission['tid'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Title</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$commission['title']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$commission['date']}}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                             <table id="budgetsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Commission Type</th>
                                        <th>Commision</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total_commision = 0;
                                    @endphp
                                    @if (!empty($commission->items))
                                    @foreach ($commission->items as $k => $item)
                                        <tr>
                                            @php
                                                $actual_commission = $item['actual_commission'];
                                                $total_commision += $actual_commission;
                                            @endphp

                                            <td>{{ $k+1 }}</td>
                                            <td>{{ $item['name'] }}</td>
                                            <td>{{ $item['phone'] }}</td>
                                            <td>{{ $item['commission_type'] }}</td>
                                            <td>{{ $item['raw_commision'] }}</td>
                                            <td>{{ $item['actual_commission'] }}</td>
                                            <input type="hidden" name="reserve_uuid[]" value="{{ $item['uuid'] }}" id="">
                                            <input type="hidden" name="commission_type[]" value="{{ $item['commision_type'] }}" id="">
                                            <input type="hidden" name="invoice_id[]" value="{{ $item['invoice_id'] }}" id="">
                                            <input type="hidden" name="invoice_amount[]" value="{{ $item['total'] }}" id="">
                                            <input type="hidden" name="quote_id[]" value="{{ $item['quote_id'] }}" id="">
                                            <input type="hidden" name="quote_amount[]" value="{{ $item['quote_amount'] }}" id="">
                                            <input type="hidden" name="name[]" value="{{ $item['name'] }}" id="">
                                            <input type="hidden" name="phone[]" value="{{ $item['phone'] }}" id="">
                                            <input type="hidden" name="id[]" value="{{ $item['id'] }}" id="">
                                        </tr>
                                    @endforeach 
                                    @endif
                                    
                                    <tfoot>
                                        <tr>
                                            <td>Total Commission</td>
                                            <td colspan="4"></td>
                                            <td>{{ numberFormat($total_commision) }}</td>
                                        </tr>
                                    </tfoot>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
