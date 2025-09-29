@extends ('core.layouts.app')

@section ('title', 'View BoQ')

@section('page-header')
    <h1>
        Manage BoQ
        <small>View BoQ</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View BoQ</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.boqs.partials.boqs-header-buttons')
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
                                            <p>BoQ No.</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{gen4tid('BoQ-',$boq['tid'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Title</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$boq['name']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @php
                                            $customer_name = '';

                                            if (!empty(@$boq->lead->customer)) {
                                                $customer_name = @$boq->lead->customer->company ?? '';

                                                if (!empty($boq->lead->branch)) {
                                                    $customer_name .= ' - ' . (@$boq->lead->branch->name ?? '');
                                                }
                                            } else {
                                                $customer_name = @$boq->lead->client_name ?? '';
                                            }

                                            // create mode
                                            $prefix = 'TKT';
                                        @endphp
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Ticket</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ gen4tid("{$prefix}-", @$boq->lead->reference) }} - {{ $customer_name }} - {{ @$boq->lead->title }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>VAT Type</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            @if ($boq['vat_type'] == 'inclusive')
                                            <p>Boq figures are VAT Inclusive</p>
                                            @elseif($boq['vat_type'] == 'exclusive') 
                                            <p>Boq figures do NOT have VAT</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Total BoQ Amount</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{numberFormat($boq['total_boq_amount'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Created At</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$boq['created_at']}}</p>
                                        </div>
                                    </div>


                                </div>

                                <div class="card-body">
                                    <table id="boqTbl" class="table-responsive pb-5 tfr my_stripe_single">
                                        <thead>
                                            <tr class="bg-gradient-directional-blue white">
                                                <th style="width: 4%;">#No</th>
                                                <th style="width: 14%;">Description (BOQ)</th>
                                                <th style="width: 14%;">Description (MTO)</th>
                                                <th style="width: 7%;">UoM (BOQ)</th>
                                                <th style="width: 7%;">UoM (MTO)</th>
                                                <th style="width: 7%;">Qty (BOQ)</th>
                                                <th style="width: 8%;">Qty (MTO)</th>
                                                <th style="width: 8%;">Rate (BOQ)</th>
                                                <th style="width: 8%;">Rate (MTO)</th>
                                                <th style="width: 10%;">{{ trans('general.rate') }} (VAT Inc MTO)</th>
                                                <th style="width: 13%;">AMT (MTO | BoQ)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                    
                                            <!-- Edit Quote or PI -->
                                            @if (isset($boq))
                                                @php
                                                   $total = 0; 
                                                @endphp
                                                @foreach ($boq->items as $k => $item)
                                                    @if ($item->type == "product")
                                                        <!-- Product Row -->
                                                        <tr class="">
                                                            @php
                                                                $amount = $item->boq_rate * $item->new_qty;
                                                                $total += $amount; 
                                                            @endphp
                                                            <td>{{$item->numbering}}</td>
                                                            <td>
                                                               {{@$item->description}}
                                                            </td>                      
                                                            <td>
                                                               {{@$item->product->name}}
                                                            </td>
                                                            <td class="{{ !$item->misc ?: 'invisible' }}">{{$item->uom}}</td>
                                                            <td>{{$item->unit}}</td>
                                                            <td class="{{ !$item->misc ?: 'invisible' }}">{{number_format($item->new_qty, 2)}}</td>
                                                            <td>{{number_format($item->qty, 2)}}</td>
                                                            <td>{{number_format($item->boq_rate, 2)}}</td>
                                                            <td class="{{ !$item->misc ?: 'invisible' }}">{{numberFormat($item->rate)}}</td>
                                                            <td>
                                                                <div class="row no-gutters ">
                                                                    <div class="col-6">
                                                                        <p class="{{ !$item->misc ?: 'invisible' }}">{{ number_format($item->product_subtotal, 2) }}</p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <p class="{{ !$item->misc ?: 'invisible' }}">({{number_format($item->tax_rate, 1)}}%)</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class='text-center {{ !$item->misc ?: 'invisible' }}'>
                                                                <span class="amount" id="amount-p{{$k}}">{{$item->amount > 0 ? numberFormat($item->amount) : numberFormat($item->boq_amount)}}</span>&nbsp;&nbsp;
                                                                {{-- <span class="lineprofit text-info" id="lineprofit-p{{$k}}">0%</span> --}}
                                                            </td>
                                                        </tr>
                                                    @else
                                                        <!-- Title Row  -->
                                                        <tr>
                                                            <td>{{$item->numbering}}</td>
                                                            <td colspan="9">
                                                                <b>{{$item->description}}</b>
                                                            </td>
                                                            <td class='text-center'>
                                                                <b>

                                                                    {{$item->amount > 0 ? numberFormat($item->amount) : numberFormat($item->boq_amount)}}
                                                                </b>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                <tfoot>
                                                    <tr>
                                                        <td></td>
                                                        <td class="text-danger"><b>Total</b></td>
                                                        <td colspan="8"></td>
                                                        <td class='text-center text-danger'><b>{{numberFormat($total)}}</b></td>
                                                    </tr>
                                                </tfoot>
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
