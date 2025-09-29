@php use App\Http\Controllers\Focus\promotions\PromoCodeReservationController; @endphp
@extends ('core.layouts.app')
@section ('title', 'Manage Invoices | View')

@php
    $isEfris = config('services.efris.base_url');
    $valid_token = token_validator('','i' . $invoice['id'].$invoice['tid'],true);
    $link = route( 'biller.print_bill',[$invoice['id'],1,$valid_token,1]);
    $link_download = route( 'biller.print_bill',[$invoice['id'],1,$valid_token,2]);
    $link_preview = route( 'biller.view_bill',[$invoice['id'],1,$valid_token,0]);
    if ($invoice['i_class'] > 1) {
        $title = trans('invoices.subscription');
        $inv_no = prefix(6).' # '.$invoice['tid'];
    } elseif ($invoice['i_class'] == 1) {
        $title = trans('invoices.pos');
        $inv_no = prefix(10).' # '.$invoice['tid'];
    } else {
        $title = trans('invoices.invoice');
        $inv_no = prefix(1).' # '.$invoice['tid'];
    }
@endphp
@section('content')
<div class="app-content">
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">View Invoice</h4>
            </div>
            <div class="col-6">
                <div class="btn-group float-right">
                    @include('focus.invoices.partials.invoices-header-buttons')
                </div>
            </div>
        </div>

        <div class="content-body">
            <section class="card">
                <div id="invoice-template" class="card-body">
                    @include('focus.invoices.partials.view-action-buttons')

                    <!-- DigiTax ETR Summary -->
                    @if ($invoice->serial_number)
                        <div class="d-flex justify-content-between round p-1 mb-1" style="background-color: rgb(224,230,239)">
                            <div>
                                <h3 class="text-dark font-weight-bold">Sale Invoice</h3>
                                <h5 class="text-dark">{{ $invoice->serial_number . '/' . $invoice->receipt_number }}</h5>
                                <h5>{{ dateFormat($invoice->invoicedate) }}</h5>
                            </div>
                            <div class="d-flex items-center justify-content-center">
                                @if ($invoice->etims_qrcode)
                                    <img src="{{ Storage::url('qr/' . $invoice->etims_qrcode) }}" alt="ETR QR Code" style="object-fit:contain" width="90" height="90" />
                                @else
                                    <div class="spinner-border spinner-border-lg mr-2 mt-2" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif  

                    <!-- Efris Summary -->
                    @if ($isEfris && $invoice->efris_invoice_no)
                        <div class="d-flex justify-content-between round p-1 mb-1" style="background-color: rgb(224,230,239)">
                            <div>
                                <h3 class="text-dark font-weight-bold">Sale Invoice</h3>
                                <h5 class="text-dark">{{ $invoice->efris_invoice_no }}</h5>
                                <h5>{{ dateFormat($invoice->invoicedate, 'd/m/Y') }}</h5>
                            </div>
                            <div class="d-flex items-center justify-content-center">
                                @if ($invoice->qrCodeImage)
                                    <img src="{{ Storage::url('qr/EfrisInvoice-' . $invoice->efris_invoice_no . '.png') }}" alt="QRCode" style="object-fit:contain" width="90" height="90" />
                                @endif
                            </div>
                        </div>
                    @endif
                    <!-- End Efris Summary -->

                    <!-- Invoice Company Details -->
                    <div id="invoice-company-details" class="row">
                        <div class="col-md-6 col-sm-12 text-center text-md-left">{{trans('general.our_info')}}
                            <div class="">
                                <img src="{{ Storage::disk('public')->url('app/public/img/company/' . config('core.logo')) }}" alt="company logo" class="avatar-100 img-responsive" />
                                <div class="media-body"><br>
                                    <ul class="px-0 list-unstyled">
                                        <li class="text-bold-800">{{(config('core.cname'))}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 text-xs-center text-md-right">
                            <h2>{{$title}}</h2>
                            <p class="pb-1">{{$inv_no}}</p>
                            @php
                            switch ($invoice['i_class']){
                            case 2: echo '<h4><span class="st-sub2">'.trans('payments.active').'</span></h4>';
                            break;
                            case 3: echo '<h4><span class="st-sub3">'.trans('payments.recurred').'</span></h4>';
                            break;
                            case 4: echo '<h4><span class="st-sub4">'.trans('payments.stopped').'</span></h4>';
                            break;
                            }
                            @endphp
                            <ul class="px-0 list-unstyled">
                                <li>{{trans('general.total')}}</li>
                                <li class="lead text-bold-800">{{amountFormat($invoice['total'])}}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Invoice Customer Details -->
                    <div id="invoice-customer-details" class="row pt-2">
                        <div class="col-sm-12 text-center text-md-left">
                            <p class="text-muted">{{trans('invoices.bill_to')}}</p>
                        </div>
                        <div class="col-md-6 col-sm-12 text-center text-md-left">
                            <ul class="px-0 list-unstyled">
                                <li class="text-bold-800"><a href="{{route('biller.customers.show',[$invoice->customer->id])}}">{{$invoice->customer->name}}</a>
                                </li>
                                <li>{{$invoice->customer->address}},</li>
                                <li>{{$invoice->customer->city}},{{$invoice->customer->region}}</li>
                                <li>{{$invoice->customer->country}}-{{$invoice->customer->postbox}}.</li>
                                <li>{{$invoice->customer->email}},</li>
                                <li>{{$invoice->customer->phone}},</li>
                                {!! custom_fields_view(1,$invoice->customer->id,false) !!}
                            </ul>
                        </div>

                        <div class="col-md-6 col-sm-12 text-center text-md-right">
                            <p>
                                <span class="text-muted">{{trans('invoices.invoice_date')}} :</span> {{dateFormat($invoice['invoicedate'])}}
                            </p>
                            <p>
                                <span class="text-muted">{{trans('invoices.invoice_due_date')}} :</span> {{dateFormat($invoice['invoiceduedate'])}}
                            </p>
                            <div class="row">
                                <div class="col">
                                    <hr>
                                    <p class=" text-danger">{{$invoice['notes']}}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items Details -->
                    <div id="invoice-items-details" class="pt-2">
                        <div class="row">
                            <div class="table-responsive col-sm-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <!-- custom col for Epicenter Africa -->  
                                            @if (auth()->user()->ins == 85) 
                                                <th>Item Code</th>
                                            @endif
                                            <th>{{trans('products.product_des')}}</th>
                                            <th class="text-right">{{trans('products.price')}}</th>
                                            <th class="text-right">{{trans('products.qty')}}</th>
                                            <th class="text-right">{{trans('general.tax')}}</th>
                                            <th class="text-right">{{trans('general.subtotal')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->products as $product)
                                            @php $prodVariation = @$product->product_variation; @endphp
                                            <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>
                                                <!-- custom col for Epicenter Africa -->  
                                                @if (auth()->user()->ins == 85) 
                                                    <td>{{ $product->cstm_project_type }}</td>
                                                @endif
                                                <td>
                                                    @if (@$prodVariation->product)
                                                        <p><a href="{{ route('biller.products.edit', $prodVariation->product) }}">{{ $product->description }}</a></p>    
                                                    @else
                                                        <p>{{ $product->description }}</p>                                                        
                                                    @endif
                                                    <p class="text-muted">
                                                    <p class="text-muted">{!!$product['product_des'] !!}</p>
                                                    </p>@if($product['serial']){{$product['serial']}}@endif
                                                </td>
                                                <td class="text-right">{{ numberFormat($product['product_price']) }}</td>
                                                <td class="text-right">{{ +$product['product_qty'] }} {{$product['unit']}}</td>
                                                @if ($product->product_amount > 0)
                                                    <td class="text-right">
                                                        {{ numberFormat($product->product_tax)}} <span class="font-size-xsmall">({{ round($product->product_tax / $product->product_price * 100)}}%)</span>
                                                    </td>
                                                    <td class="text-right">{{ numberFormat($product->product_price * $product['product_qty'])}}</td>
                                                @else
                                                    <td class="text-right">{{ numberFormat(0)}} <span class="font-size-xsmall">(0%)</span></td>
                                                    <td class="text-right">{{ numberFormat($product->product_price)}}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-7 col-sm-12 text-center text-md-left">
                                <p class="lead">{{trans('payments.payment_status')}}:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-md">
                                            <tbody>
                                                <tr>
                                                    <td>{{trans('payments.payment_status')}}:</td>
                                                    <td id="status" class="badge st-{{$invoice['status']}}">{{trans('payments.'.$invoice['status'])}}</td>
                                                </tr>
                                                @if($invoice['pmethod'])
                                                    <tr>
                                                        <td>{{trans('general.payment_method')}}:</td>
                                                        <td id="method">{{$invoice['pmethod']}}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @php
                                $promoDiscounts = @$invoice->promoDiscounts;
                                $discountsTable = null;
                                if($promoDiscounts) $discountsTable = (new PromoCodeReservationController())->generateDiscountsTable($promoDiscounts)
                            @endphp
                            @if($discountsTable)
                                <div class="col-12 row mt-4 mb-4">
                                    <div class="col-12">
                                        <label id="promoDiscounts" class="mt-1 col-12"> {!! $discountsTable !!}</label>
                                    </div>
                                </div>
                            @endif
                            <!-- Summary totals -->
                            <div class="col-12 row d-flex justify-content-end">
                                <div class="col-md-5 col-sm-12">
                                    <p class="lead">{{trans('general.summary')}}</p>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tbody>
                                            @php
                                                $label = $invoice->products()->where('product_tax', '>', 0)->exists()? 'product_tax' : null;
                                                if (!$label) $label = $invoice->products()->where('tax_rate', '>', 0)->exists()? 'tax_rate' : 'product_tax';
                                                if ($label) {
                                                    $non_taxable_amount = $invoice->products()->where($label, 0)->sum(DB::raw('product_qty*product_price'));
                                                }
                                            @endphp
                                            @if ($invoice->taxable > 0)
                                                <tr>
                                                    <td>Taxable Total</td>
                                                    <td class="text-right">{{amountFormat($invoice->taxable, $invoice->currency_id)}}</td>
                                                </tr>
                                            @endif
                                            @if (@$non_taxable_amount)
                                                <tr>
                                                    <td>Non-Taxable Total</td>
                                                    <td class="text-right">{{amountFormat($non_taxable_amount, $invoice->currency_id)}}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td>{{trans('general.subtotal')}}</td>
                                                <td class="text-right">{{amountFormat($invoice['subtotal'], $invoice->currency_id)}}</td>
                                            </tr>
                                            <tr>
                                                <td>VAT</td>
                                                <td class="text-right">{{amountFormat($invoice['tax'], $invoice->currency_id)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-bold-800">Grand Total</td>
                                                <td class="text-bold-800 text-right">{{amountFormat($invoice['total'], $invoice->currency_id)}}</td>
                                            </tr>
                                            <tr>
                                                <td>{{trans('general.payment_made')}}</td>
                                                <td class="text-primary text-right">(-) <span id="payment_made">{{ amountFormat($invoice->amountpaid, $invoice->currency_id) }}</span>
                                                </td>
                                            </tr>
                                            <tr class="bg-grey bg-lighten-4">
                                                <td class="text-bold-800">{{trans('general.balance_due')}}</td>
                                                <td class="text-bold-800 text-right text-danger" id="payment_due"> {{ amountFormat($invoice->total - $invoice->amountpaid, $invoice->currency_id) }}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center">
                                        <p>{{trans('general.authorized_person')}}</p>
                                        <img src="{{ Storage::disk('public')->url('app/public/img/signs/' . @$invoice->user->signature) }}" alt="signature" class="height-100 m-2" />
                                        <h6>{{@$invoice->user->first_name}} {{@$invoice->user->last_name}}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! custom_fields_view(2,$invoice['id']) !!}

                    <!-- Invoice Footer -->
                    <div id="invoice-footer">
                        <div class="row">
                            <div class="col-md-7 col-sm-12">
                                <h5>{{trans('general.payment_terms')}}</h5>
                                <hr>
                                <h5>{{@$invoice->term->title}}</h5>
                                <p>{!! @$invoice->term->terms !!}</p>
                            </div>
                            <div class="col-md-5 col-sm-12 text-center">
                                @if($invoice['status']!='canceled') <a href="#sendEmail" data-toggle="modal" data-remote="false" data-type="1" data-type1="notification" class="btn btn-primary btn-lg my-1 send_bill"><i class="fa fa-paper-plane-o"></i> {{trans('general.send')}}
                                </a>@endif
                            </div>
                        </div>
                    </div>

                    <!--/ Invoice Footer -->
                    <div class="row mt-2">

                        <div class="col-md-12">
                            <p class="lead">{{trans('transactions.transactions')}}</p>
                            <table class="table table-bordered table-md table-striped">
                                @if(isset($invoice->transactions[0]))
                                <thead>
                                    <th>#</th>
                                    <th>{{trans('transactions.payment_date')}}</th>
                                    <th class="">{{trans('transactions.method')}}</th>
                                    <th class="text-right">{{trans('transactions.debit')}}</th>
                                    <th class="text-right">{{trans('transactions.credit')}}</th>
                                    <th class="">{{trans('transactions.note')}}</th>
                                </thead> @endif
                                <tbody id="transaction_activity">
                                    @foreach($invoice->transactions as $transaction)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>
                                            <p class="text-muted"><a href="{{route('biller.print_payslip',[$transaction['id'],1,1])}}" class="btn btn-blue btn-sm"><span class="fa fa-print" aria-hidden="true"></span></a> {{$transaction['payment_date']}}
                                            </p>
                                        </td>
                                        <td class="">{{$transaction['method']}}</td>
                                        <td class="text-right">{{amountFormat($transaction['debit'])}}</td>
                                        <td class="text-right">{{numberFormat($transaction['credit'])}}</td>
                                        <td class="">{{$transaction['note']}}</td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <p class="lead">{{trans('general.attachment')}}</p>
                            <pre>{{trans('general.allowed')}}: {{$features['value1']}} </pre>
                            <!-- The fileinput-button span is used to style the file input field as button -->
                            <div class="btn btn-success fileinput-button display-block col-2">
                                <i class="glyphicon glyphicon-plus"></i>
                                <span>Select files...</span>
                                <!-- The file input field used as target for the file upload widget -->
                                <input id="fileupload" type="file" name="files">
                            </div>
                        </div>
                    </div>
                    <!-- The global progress bar -->
                    <div id="progress" class="progress progress-sm mt-1 mb-0 col-md-3">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <!-- The container for the uploaded files -->
                    <table id="files" class="files table table-striped mt-2">
                        @foreach($invoice->attachment as $row)
                        <tr>
                            <td><a data-url="{{route('biller.bill_attachment')}}?op=delete&id={{$row['id']}}" class="aj_delete red"><i class="btn-sm fa fa-trash"></i></a> <a href="{{ Storage::disk('public')->url('app/public/files/' . $row['value']) }}" class="purple"><i class="btn-sm fa fa-eye"></i> {{$row['value']}}</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    <br>
                </div>
            </section>
        </div>
    </div>
</div>
@if ($isEfris)
    @include('focus.invoices.partials.validation-preview-modal')
@endif

@include("focus.invoices.partials.cancel-invoice-modal")
@include("focus.invoices.partials.send_invoices")
{{-- @include("focus.modal.payment_model",array('category'=>0)) --}}
{{-- @include("focus.modal.email_model",array('category'=>1)) --}}
{{-- @include("focus.modal.sms_model",array('category'=>2)) --}}
{{-- @include("focus.modal.status_model") --}}
{{-- @include("focus.modal.subscription_model") --}}
@endsection

@section('extra-style')
{!! Html::style('focus/jq_file_upload/css/jquery.fileupload.css') !!}
@endsection

@section('extra-scripts')
{{ Html::script('focus/jq_file_upload/js/jquery.fileupload.js') }}
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        summernote: {
            height: 150,
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['fullscreen', ['fullscreen']],
                ['codeview', ['codeview']]
            ],
            popover: {}
        },
        fileupload: {
            url: "{{ route('biller.bill_attachment') }}",
            dataType: 'json',
            formData: {
                _token: "{{ csrf_token() }}",
                id: "{{$invoice['id']}}",
                bill: 1
            },
            done: function(e, data) {
                const tr = `<tr>
                    <td><a data-url="{{route('biller.bill_attachment')}}?op=delete&id= ' + file.id + ' " class="aj_delete red">
                    <i class="btn-sm fa fa-trash"></i></a> ' + file.name + ' </td></tr>`;
                $.each(data.result, function(index, file) { $('#files').append(tr) });
            },
            progressall: function(e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css('width', progress + '%' );
            }
        },
    };

    const View = {
        init() {
            $.ajaxSetup(config.ajax);
            $('.summernote').summernote(config.summernote);

            $('#confirmInvoiceBtn').click(View.validateETRInvoice);

            $(document).on('click', ".aj_delete", View.clickDeleteItem);
            $('#fileupload').fileupload(config.fileupload)
                .prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? void(0) : 'disabled');
        },

        validateETRInvoice() {
            $('#validationPreviewModal').modal('hide');
            swal({
                title: 'Are You  Sure?',
                text: "Once applied, you will not be able to undo!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((isOk) => {
                if (isOk) {                                
                    addObject({
                        // url: "{{ route('biller.digitax.validation') }}", 
                        // form: {invoice_id: $(this).attr('data-id')},
                        url: "{{ route('biller.invoices.efris_validate') }}",
                        form: {
                            invoice_id: $('#validationPreviewModal').attr('data-id'),
                        }
                    }, true);
                }
            }); 
        },

        clickDeleteItem(e) {
            e.preventDefault();
            const el = $(this);
            $.ajax({
                url: $(this).attr('data-url'),
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    el.closest('tr').remove();
                    el.remove();
                }
            });
        },
    };

    $(View.init);
</script>
@endsection