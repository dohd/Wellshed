@php
    $isEfris = config('services.efris.base_url');
    $is_debit = $creditnote['is_debit'];
@endphp
@extends ('core.layouts.app')
@section('title', $is_debit ? 'Debit Notes Management' : 'Customer Credit Notes Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4>{{ $is_debit ? 'Debit Notes Management' : 'Customer Credit Notes Management' }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.creditnotes.partials.creditnotes-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header pb-0">
            <div class="btn-group">
                <!-- DigiTax local currency -->
                {{-- @if (@$creditnote->currency->rate == 1 && config('services.digitax.api_key')) --}}
                @if (false)
                    @if ($creditnote->digitax_id && !$creditnote->etims_url)
                        <button class="btn btn-purple" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $creditnote->serial_number? 'QRCode Processing' : 'ETR Processing' }} 
                        </button>
                    @endif
                    @if (!$creditnote->digitax_id)
                        <a href="#" class="btn btn-purple mb-1" data-id="{{ $creditnote->id }}" id="validate-btn">
                            <i class="fa fa-check-square-o" aria-hidden="true"></i> ETR Validation
                        </a>
                    @endif
                @endif

                <!-- EFRIS Validate Button  -->
                @if ($isEfris)
                    <a href="#" class="btn btn-purple ml-1 mr-1" id="validate-btn" data-toggle="modal" data-target="#validationPreviewModal">
                        <i class="fa fa-check-square-o" aria-hidden="true"></i> ETR Validation
                    </a>
                    {{ Form::open(['route' => 'biller.creditnotes.efris_query', 'method' => 'POST']) }}
                        {{ Form::hidden('creditnote_id', $creditnote->id) }}
                        <button type="submit" class="btn btn-info">
                            <i class="fa fa-refresh" aria-hidden="true"></i> Query Status
                        </button>
                    {{ Form::close() }}
                @endif
            </div>
        </div>  
        <div class="card-body">  
            <!-- DigiTax ETR Summary -->
            @if ($creditnote->serial_number)
                <div class="d-flex justify-content-between round p-1 mb-1" style="background-color: rgb(224,230,239)">
                    <div>
                        <h3 class="text-dark font-weight-bold">Credit Note</h3>
                        <h5 class="text-dark">{{ $creditnote->serial_number . '/' . $creditnote->receipt_number }}</h5>
                        <h5>{{ dateFormat($creditnote->date) }}</h5>
                    </div>
                    <div class="d-flex items-center justify-content-center">
                        @if ($creditnote->etims_qrcode)
                            <img src="{{ Storage::url('qr/' . $creditnote->etims_qrcode) }}" alt="ETR QR Code" style="object-fit:contain" width="90" height="90" />
                        @else
                            <div class="spinner-border spinner-border-lg mr-2 mt-2" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif     
            <!-- End DigiTax ETR Summary -->   

            <!-- Efris Summary -->
            @if ($isEfris)
                @if ($creditnote->efris_approval_status_name == 'approved')
                    <div class="d-flex justify-content-between round p-1 mb-1" style="background-color: rgb(224,230,239)">
                        <div>
                            <h3 class="text-dark font-weight-bold">Sale Credit Note</h3>
                            <h5 class="text-dark">{{ $creditnote->efris_creditnote_no }}</h5>
                            <h5>{{ dateFormat($creditnote->date, 'd/m/Y') }}</h5>
                        </div>
                        <div class="d-flex items-center justify-content-center">
                            @if ($creditnote->qrCodeImage)
                                <img src="{{ Storage::url('qr/EfrisCreditNote-' . $creditnote->efris_creditnote_no . '.png') }}" alt="QRCode" style="object-fit:contain" width="90" height="90" />
                            @endif
                        </div>
                    </div>
                @elseif ($creditnote->efris_approval_status_name == 'submitted') 
                    <div class="d-flex justify-content-between round p-1 mb-1" style="background-color: rgb(224,230,239)">
                        <div>
                            <h3 class="text-dark font-weight-bold">Sale Credit Note</h3>
                            <h5 class="text-dark">{{ $creditnote->efris_reference_no }}</h5>
                            <h5>{{ dateFormat($creditnote->date, 'd/m/Y') }}</h5>
                        </div>
                        <div class="d-flex items-center justify-content-center">
                            <div class="spinner-border spinner-border-lg mr-2 mt-2" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            <!-- End Efris Summary -->

            <div class="row mb-1">
                <div class="col-6">
                    <table id="customer-table" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>  
                            @php   
                                $details = [
                                    'Serial No.' => gen4tid($is_debit? 'DN-' : 'CN-', $creditnote->tid),
                                    'Date' => dateFormat($creditnote->date),
                                    'Invoice No.' => gen4tid('INV-', @$creditnote->invoice->tid),
                                    'Customer' => @$creditnote->customer->company ?: @$creditnote->customer->name,
                                    'Note' => $creditnote->note,
                                ];                       
                            @endphp
                            @foreach ($details as $key => $val)
                                <tr>
                                    <th width="50%">{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach     
                            <tr><td>&nbsp;</td><td></td></tr>                                               
                        </tbody>
                    </table>            
                </div>
                <div class="col-6">
                    <table class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                        <tbody>  
                            @php   
                                $details = [
                                    'Currency' => @$creditnote->currency->code . ' / ' . floatval($creditnote->fx_curr_rate),
                                    'Tax Rate' => +$creditnote->tax_id,
                                    'Taxable' => numberFormat($creditnote->taxable),
                                    'Subtotal' => numberFormat($creditnote->subtotal),
                                    'Tax' => numberFormat($creditnote->tax),
                                    'Total' => numberFormat($creditnote->total),
                                ];                       
                            @endphp
                            @foreach ($details as $key => $val)
                                <tr>
                                    <th width="50%">{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr>
                            @endforeach                                                        
                        </tbody>
                    </table> 
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                    <tr>
                        <th>#</th>
                        <th width="40%">Item Description</th>
                        <th>Item Ref.</th>
                        <th>Qty</th>
                        <th>UoM</th>
                        <th>Price</th>
                        <th>Tax</th>  
                        <th>Taxable Amount</th>                          
                        <th>Total Amount</th>
                    </tr>
                    <tbody>
                        @foreach ($creditnote->items as $item)
                            <tr>
                                <th>{{ $item->numbering }}</th>
                                <td>{{ $item->name }}</td>
                                <td>
                                    @php
                                        if ($item->productvar) echo $item->productvar->code;
                                        if (@$item->invoice_item->product_variation) echo $item->invoice_item->product_variation->code;
                                        if (@$item->invoice_item->quote) {
                                            $quote = @$item->invoice_item->quote;
                                            echo gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
                                        }
                                    @endphp
                                </td>
                                <td>{{ +$item->qty }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ numberFormat($item->rate) }}</td>                                        
                                <td>{{ numberFormat($item->tax) }}</td>
                                <td>{{ numberFormat($item->taxable) }}</td>
                                <td>{{ numberFormat($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@if ($isEfris)
    @include('focus.creditnotes.partials.validation-preview-modal')
@endif
@endsection

@section('extra-scripts')
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
    };

    const View = {
        init() {
            $.ajaxSetup(config.ajax);
            $('#confirmInvoiceBtn').click(View.validateETRCreditNote);
        },

        validateETRCreditNote() {
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
                        // form: {creditnote_id: $(this).attr('data-id')},
                        url: "{{ route('biller.creditnotes.efris_validate') }}",
                        form: {
                            creditnote_id: $('#validationPreviewModal').attr('data-id'),
                        }
                    }, true);
                }
            }); 
        },
    };

    $(View.init);
</script>
@endsection
