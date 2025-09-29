@extends ('core.layouts.public_app')
@section ('title', $general['bill_type'] . ' | ' . $company['cname'])
@section ('icon',  $company['icon'])
@section('content')
    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div id="notify" class="alert alert-success" style="display:none;">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>

                    <div class="message"></div>
                </div>


                <div id="invoice-template" class="card-body">
                    <div class="row wrapper white-bg page-heading">

                        <div class="col">
                            @php
                                $remaining = $resource['total'] - $resource['pamnt'];
                            @endphp
                            @if($resource['status'] != 'canceled')
                                <div class="row">


                                    <div class="col-md-8">
                                        <div class="form-group mt-2">
                                            @if($general['status_block'])

                                                {{trans('payments.payment')}}:

                                                @if($online_payment)
                                                    <a class="btn btn-success text-white btn-min-width mr-1"
                                                       data-toggle="modal" data-target="#paymentCard"><i
                                                                class="fa fa-cc"></i> {{trans('payments.credit_card')}}
                                                    </a>
                                                @endif

                                                <a class="btn btn-secondary btn-min-width mr-1"
                                                   href="{{$link['bank']}}" role="button"><i
                                                            class="fa fa-bank"></i> {{trans('payments.bank')}}
                                                    - {{trans('payments.cash')}}</a>
                                            @endif
                                            @if ($logged_in_user)
                                                <a class="btn btn-warning  mr-1"
                                                   href="{{$resource['url']}}"
                                                   role="button"><i
                                                            class="fa fa-backward"></i> </a>

                                            @endif

                                        </div>
                                    </div>


                                    <div class="col-md-4 text-right">
                                        <div class="btn-group mt-2">
                                            <button type="button" class="btn btn-primary btn-min-width dropdown-toggle"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                                        class="fa fa-print"></i> {{trans('general.print')}}
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                   href="{{$link['link']}}">{{trans('general.print')}}</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item"
                                                   href="{{$link['download']}}">{{trans('general.pdf')}}</a>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="title-action ">


                                </div>
                            @else
                                <h2 class="btn btn-oval btn-danger">{{trans('payments.'.$resource['status'])}}</h2>
                            @endif
                        </div>
                    </div>

                    <!-- Invoice Company Details -->
                    <div id="invoice-company-details" class="row mt-2">
                        <div class="col-md-6 col-sm-12 text-xs-center text-md-left"><p></p>
                            <img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company['logo']) }}"
                                 class="img-responsive p-1 m-b-2" style="max-height: 120px;">
                            <p class="text-muted">{{trans('general.our_info')}}</p>


                            <ul class="px-0 list-unstyled">
                                <li> {{$company['cname']}}</li>
                                <li>
                                    {{$company['address']}},
                                </li>
                                <li>
                                    {{$company['city']}}, {{$company['region']}}</li>
                                <li>
                                    {{$company['country']}} - {{$company['postbox']}}</li>
                                <li>
                                    {{trans('general.phone')}}: {{$company['phone']}}</li>
                                <li>
                                    {{trans('general.email')}}: {{$company['email']}}</li>
                                @if($company['taxid'])
                                    <li>{{$general['tax_id']}}: {{$company['taxid']}}</li>
                                @endif
                                 {!! custom_fields_view(6,$resource['ins'],false,$resource['ins']) !!}
                            </ul>
                        </div>
                        <div class="col-md-6 col-sm-12 text-xs-center text-md-right mt-2">
                            <h2>{{$general['bill_type']}}</h2>
                            <p class="pb-1"> {{prefix($general['prefix'],$resource['ins'])}} # {{$resource['tid']}}</p>
                            <p class="pb-1">{{trans('general.reference')}} : {{$resource['refer']}}</p>
                            <ul class="px-0 list-unstyled">
                                <li>{{trans('general.gross_amount')}}</li>
                                <li class="lead text-bold-800"> {{amountFormat($resource['total'],$resource['currency'])}}</li>
                            </ul>
                        </div>

                    </div>


                    <!--/ Invoice Company Details -->

                    <!-- Invoice Customer Details -->


                    <div class="row pt-3">
                        <div class="col-md-5 col-sm-12 text-xs-center text-md-left">

                            <p class="text-muted">{{trans('invoices.bill_to')}}</p>
                            <ul class="px-0 list-unstyled">


                                <li class="text-bold-800"><strong>  {{$resource->customer->name}}</strong></li>
                                <li>{{$resource->customer->address}},</li>
                                <li>{{$resource->customer->city}},{{$resource->customer->region}}</li>
                                <li>{{$resource->customer->country}}-{{$resource->customer->postbox}}.</li>
                                <li>{{$resource->customer->email}},</li>
                                <li>{{$resource->customer->phone}},</li>
                                @if($resource->customer->taxid)
                                    <li>{{$general['tax_id']}}: {{$resource->customer->taxid}}</li>@endif
                                {!! custom_fields_view($resource['person'],$resource['person_id'],false,$resource['ins']) !!}
                            </ul>


                        </div>
                        <div class="col-md-4 col-sm-12 text-xs-center text-md-left">@if ($resource->customer->name_s)
                                <p class="text-muted">{{trans('customers.address_s')}}</p>
                                <ul class="px-0 list-unstyled">


                                    <li class="text-bold-800"><strong>  {{$resource->customer->name_s}}</strong></li>
                                    <li>{{$resource->customer->address_s}},</li>
                                    <li>{{$resource->customer->city_s}},{{$resource->customer->region_s}}</li>
                                    <li>{{$resource->customer->country_s}}-{{$resource->customer->postbox_s}}.</li>
                                    <li>{{$resource->customer->email_s}},</li>
                                    <li>{{$resource->customer->phone_s}},</li>

                                </ul>
                            @endif
                        </div>
                        <div class="col-md-3 col-sm-12 text-md-right">
                            @php
                                $date_text = $general['lang_bill_due_date'];
                               $fill=false;
                            @endphp

                            <p><span class="text-muted">{{$general['lang_bill_date']}}</span>
                                : {{dateFormat($resource['invoicedate'],$company['main_date_format'])}}</p>
                            <p><span class="text-muted">{{$general['lang_bill_due_date']}}</span>
                                : {{dateFormat($resource['invoiceduedate'],$company['main_date_format'])}}</p>
                            <p><span class="text-muted">{{trans('general.payment_terms')}}</span>
                                : {{@$resource->term->title}}</p>
                        </div>
                    </div>

                    <!--/ Invoice Customer Details -->
                    @if(isset($resource['proposal']))
                        <div class="row">
                            <div class="col">

                                <hr>

                                <p>{!! $resource['proposal']  !!}}</p>
                            </div>

                        </div>@endif
                <!-- Invoice Items Details -->
                    <div id="invoice-items-details" class="pt-2">
                        <div class="row">
                            <div class="table-responsive col-sm-12">
                                <table class="table table-striped">
                                    <thead>

                                    @if($resource['tax_format']=='exclusive' OR $resource['tax_format']=='inclusive')
                                        <tr>
                                            <th>#</th>
                                            <th> {{trans('products.product_des')}}</th>
                                            <th class="text-xs-left">{{trans('products.qty')}}</th>
                                            <th class="text-xs-left">{{trans('products.price')}}</th>
                                            <th class="text-xs-left">{{trans('general.tax')}}</th>
                                            <th class="text-xs-left">{{trans('general.discount')}}</th>
                                            <th class="text-xs-left">{{trans('general.subtotal')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($resource->products as $product)

                                        @php
                                            if ($fill == true) {
                                              $flag = ' m_fill';
                                          } else {
                                              $flag = '';
                                          }
                                           $fill = !$fill;
                                        @endphp
                                        <tr class="product_row {{$flag}}">
                                            <td style="width: 1rem;">
                                                {{$loop->iteration}}
                                            </td>
                                            <td>
                                                {{$product['product_name']}} @if(isset($product['serial'])){{$product['serial']}}@endif
                                            </td>
                                            <td>
                                                {{numberFormat($product['product_qty'])}} {{$product['unit']}}
                                            </td>
                                            <td>
                                                {{amountFormat($product['product_price'],$resource['currency'])}}
                                            </td>


                                            <td>{{amountFormat($product['total_tax'],$resource['currency'])}} <span
                                                        class="font-size-xsmall">({{numberFormat($product['product_tax'],$resource['currency'])}}%)</span>
                                            </td>


                                            <td>{{amountFormat($product['total_discount'],$resource['currency'])}}</td>

                                            <td>
                                                {{amountFormat($product['product_subtotal'],$resource['currency'])}}
                                            </td>
                                        </tr>
                                        @if($product['product_des'])
                                            <tr class="product_row  {{$flag}}">
                                                <td style="width: 1rem;">

                                                </td>
                                                <td class="" colspan="4">  {!!$product['product_des'] !!} </td>

                                            </tr>
                                        @endif
                                        @if(isset($product->variation->id))
                                            <tr>
                                                <td colspan="7">{!! custom_fields_view(3,@$product->variation->id,false,true) !!}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                    @endif

                                    @if($resource['tax_format']=='cgst')
                                        <tr>
                                            <th>#</th>
                                            <th> {{trans('products.product_des')}}</th>
                                            <th class="text-xs-left">{{trans('products.qty')}}</th>
                                            <th class="text-xs-left">{{trans('products.price')}}</th>
                                            <th class="text-xs-left">{{trans('general.cgst')}}</th>
                                            <th class="text-xs-left">{{trans('general.sgst')}}</th>
                                            <th class="text-xs-left">{{trans('general.discount')}}</th>
                                            <th class="text-xs-left">{{trans('general.subtotal')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($resource->products as $product)
                                            @php
                                                if ($fill == true) {
                                                  $flag = ' m_fill';
                                              } else {
                                                  $flag = '';
                                              }
                                               $fill = !$fill;

                                            @endphp
                                            <tr class="product_row {{$flag}}">
                                                <td style="width: 1rem;">
                                                    #
                                                </td>
                                                <td>
                                                    {{$product['product_name']}}
                                                </td>
                                                <td>
                                                    {{numberFormat($product['product_qty'])}} {{$product['unit']}}
                                                </td>
                                                <td>
                                                    {{amountFormat($product['product_price'],$resource['currency'])}}
                                                </td>


                                                <td>{{amountFormat($product['total_tax']/2,$resource['currency'])}} <span
                                                            class="font-size-xsmall">({{numberFormat($product['product_tax']/2,$resource['currency'])}}%)</span>
                                                </td>
                                                <td>{{amountFormat($product['total_tax']/2,$resource['currency'])}} <span
                                                            class="font-size-xsmall">({{numberFormat($product['product_tax']/2,$resource['currency'])}}%)</span>
                                                </td>


                                                <td>{{amountFormat($product['total_discount'],$resource['currency'])}}</td>

                                                <td>
                                                    {{amountFormat($product['product_subtotal'],$resource['currency'])}}
                                                </td>
                                            </tr>

                                            @if($product['product_des'])
                                                <tr class="product_row  {{$flag}}">
                                                    <td style="width: 1rem;">

                                                    </td>
                                                    <td class="" colspan="4">{{$product['product_des']}}</td>

                                                </tr>
                                            @endif
                                            @if(isset($product->variation->id))
                                                <tr>
                                                    <td colspan="7">{!! custom_fields_view(3,@$product->variation->id,false,true) !!}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    @endif

                                    @if($resource['tax_format']=='igst')
                                        <tr>
                                            <th>#</th>
                                            <th> {{trans('products.product_des')}}</th>
                                            <th class="text-xs-left">{{trans('products.qty')}}</th>
                                            <th class="text-xs-left">{{trans('products.price')}}</th>
                                            <th class="text-xs-left">{{trans('general.igst')}}</th>
                                            <th class="text-xs-left">{{trans('general.discount')}}</th>
                                            <th class="text-xs-left">{{trans('general.subtotal')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($resource->products as $product)
                                            @php
                                                if ($fill == true) {
                                                  $flag = ' m_fill';
                                              } else {
                                                  $flag = '';
                                              }
                                               $fill = !$fill;

                                            @endphp
                                            <tr class="product_row {{$flag}}">
                                                <td style="width: 1rem;">
                                                    #
                                                </td>
                                                <td>
                                                    {{$product['product_name']}}
                                                </td>
                                                <td>
                                                    {{numberFormat($product['product_qty'])}} {{$product['unit']}}
                                                </td>
                                                <td>
                                                    {{amountFormat($product['product_price'],$resource['currency'])}}
                                                </td>


                                                <td>{{amountFormat($product['total_tax'],$resource['currency'])}} <span
                                                            class="font-size-xsmall">({{numberFormat($product['product_tax'],$resource['currency'])}}%)</span>
                                                </td>


                                                <td>{{amountFormat($product['total_discount'],$resource['currency'])}}</td>

                                                <td>
                                                    {{amountFormat($product['product_subtotal'],$resource['currency'])}}
                                                </td>
                                            </tr>
                                            @if($product['product_des'])
                                                <tr class="product_row  {{$flag}}">
                                                    <td style="width: 1rem;">

                                                    </td>
                                                    <td class="" colspan="4">{{$product['product_des']}}</td>

                                                </tr>
                                            @endif
                                            @if(isset($product->variation->id))
                                                <tr>
                                                    <td colspan="7">{!! custom_fields_view(3,@$product->variation->id,false,true) !!}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    @endif


                                </table>
                            </div>
                        </div>
                        <p></p>
                        <div class="row">
                            <div class="col-md-7 col-sm-12 text-xs-center text-md-left">


                                <div class="row">
                                    <div class="col-md-8"><p
                                                class="lead">{{trans('general.status')}}:
                                            <u><strong
                                                        id="pstatus">{{trans('payments.'.$resource['status'])}}</strong></u>
                                        </p>
                                        @if($resource['pmethod'])
                                            <p class="lead">{{trans('general.payment_method')}}: <u><strong
                                                            id="pmethod">{{$resource['pmethod']}}</strong></u>
                                            </p>
                                        @endif

                                        <p class="lead mt-1"><br>{{trans('general.note')}}:</p>
                                        <code>
                                            {{$resource['notes']}}
                                        </code>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-5 col-sm-12">
                                <p class="lead">{{trans('general.summary')}}</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <td>{{trans('general.subtotal')}}</td>
                                            <td class="text-xs-right"> {{amountFormat($resource['subtotal'],$resource['currency'])}}</td>
                                        </tr>
                                        @if($resource['tax']>0)
                                            <tr>
                                                <td>{{$general['tax_string_total']}}</td>
                                                <td class="text-xs-right">{{amountFormat($resource['tax'],$resource['currency'])}}</td>
                                            </tr>@endif
                                        @if($resource['discount']>0)
                                            <tr>
                                                <td>{{trans('general.discount')}}</td>
                                                <td class="text-xs-right">{{amountFormat($resource['discount'],$resource['currency'])}}</td>
                                            </tr>@endif
                                        @if($resource['shipping']>0)
                                            <tr>
                                                <td>{{trans('general.shipping')}}</td>
                                                <td class="text-xs-right">{{amountFormat($resource['shipping'],$resource['currency'])}}</td>
                                            </tr>
                                            @if($resource['ship_tax']>0)
                                                <tr>
                                                    <td>{{trans('general.shipping_tax')}}
                                                        ({{trans('general.'.$resource['ship_tax_type'])}})
                                                    </td>
                                                    <td>{{amountFormat($resource['ship_tax'],$resource['currency'])}}</td>
                                                </tr>@endif
                                        @endif
                                        <tr>
                                            <td class="text-bold-800">{{trans('general.total')}}</td>
                                            <td class="text-bold-800">{{amountFormat($resource['total'],$resource['currency'])}}</td>
                                        </tr>
                                        @if( $general['status_block'])
                                            <tr>
                                                <td>{{trans('general.payment_made')}}</td>
                                                <td class="pink">(-) <span
                                                            id="payment_made">{{amountFormat($resource['pamnt'],$resource['currency'])}}</span>
                                                </td>
                                            </tr>
                                            <tr class="bg-grey bg-lighten-4">
                                                <td class="text-bold-800">{{trans('general.balance_due')}}</td>
                                                <td class="text-bold-800"
                                                    id="payment_due"> {{amountFormat($resource['total']-$resource['pamnt'],$resource['currency'])}}</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <p><strong>{{trans('general.authorized_person')}}</strong></p>
                                    <img src="{{ Storage::disk('public')->url('app/public/img/signs/' . $resource->user->signature) }}"
                                         alt="signature" class="height-100 m-2"/>
                                    <h6>({{$resource->user->first_name}} {{$resource->user->last_name}})</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Footer -->

                    <div id="invoice-footer">
                        @if(isset($resource->transactions[0]))
                            <p class="lead">{{trans('transactions.transactions')}}
                                :</p>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{trans('transactions.payment_date')}}</th>
                                    <th class="">{{trans('transactions.method')}}</th>
                                    <th class="text-right">{{trans('transactions.debit')}}</th>
                                    <th class="text-right">{{trans('transactions.credit')}}</th>
                                    <th class="">{{trans('general.note')}}</th>


                                </tr>
                                </thead>
                                <tbody id="activity">
                                @foreach($resource->transactions as $transaction)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>
                                            <p class="text-muted">{{$transaction['payment_date']}}</p>
                                        </td>
                                        <td class="">{{$transaction['method']}}</td>
                                        <td class="text-right">{{amountFormat($transaction['debit'],$resource['currency'])}}</td>
                                        <td class="text-right">{{amountFormat($transaction['credit'],$resource['currency'])}}</td>
                                        <td class="">{{$transaction['note']}}</td>

                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                        <hr>
                        {!! custom_fields_view($resource['custom'],$resource['id'],true,$resource['ins']) !!}
                        <div class="row">

                            <div class="col-md-7 col-sm-12">


                                <h5>{{@$resource->term->title}}</h5>
                                <p>{!! @$resource->term->terms !!}}</p>
                            </div>

                        </div>


                    </div>
                    <!--/ Invoice Footer -->

                    @if(isset($resource->attachment))
                        <table id="files" class="files table table-striped mt-2">
                            @foreach($resource->attachment as $row)
                                <tr>
                                    <td>
                                        <a href="{{ Storage::disk('public')->url('app/public/files/' . $row['value']) }}"
                                           class="purple"><i class="btn-sm fa fa-eye"></i> {{$row['value']}}</a></td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
                </section>
            </div>
        </div>
    </div>
    @if($online_payment)
        <div id="paymentCard" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">

                        <h4 class="modal-title">{{trans('general.make_payment')}}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        @foreach ($gateway as $row)

                            @if($row->config['enable']=='Yes')
                                @php
                                    $cid = $row['id'];
                                    $title = $row['name'];
                                    if ($row->config['surcharge'] > 0) {
                                        $surcharge_t = true;
                                        $fee = '( ' . amountFormat($resource['total']-$resource['pamnt'],$resource['currency']) . '+' . numberFormat($row->config['surcharge']) . ' %)';
                                    } else {
                                        $fee = '';
                                    }
                                @endphp
                                <a href="{{$link['payment']}}?g={{$row['id']}}"
                                   class="btn mb-1 btn-block blue rounded border border-info text-bold-700 border-lighten-5 "><span
                                            class=" display-block"><span
                                                class="grey">{{trans('payments.pay_with')}} </span><span
                                                class="blue font-medium-2">{{$row['name']}} {{$fee}}</span></span><br>

                                    <img class="mt-1 bg-white round" style="max-width:20rem;max-height:10rem"
                                         src="{{ Storage::disk('public')->url('app/public/img/gateway_logo/' . $row['id'].'.png') }}">
                                </a><br>
                            @endif
                        @endforeach
                    </div>

                </div>

            </div>
        </div>
    @endif
@endsection