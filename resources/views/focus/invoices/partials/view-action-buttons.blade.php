<div class="row">
    <!-- ETR for local currency -->
    {{-- @if (@$invoice->currency->rate == 1 && config('services.digitax.api_key')) --}}
    @if (false)
        <div class="col-md-2">
            @if ($invoice->digitax_id && !$invoice->etims_url)
                <button class="btn btn-purple mb-1 ml-1" type="button" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ $invoice->serial_number? 'QRCode Processing' : 'ETR Processing' }} 
                </button>
            @endif
            @if (!$invoice->digitax_id)
                <a href="#" class="btn btn-purple mb-1 ml-1" data-id="{{ $invoice->id }}" id="validate-btn">
                    <i class="fa fa-check-square-o" aria-hidden="true"></i> ETR Validation
                </a>
            @endif
        </div>
    @endif

    <!-- EFRIS Validate Button  -->
    @if (config('services.efris.base_url'))
        <a href="#" class="btn btn-purple mb-1 ml-1" data-id="{{ $invoice->id }}" id="validate-btn" data-toggle="modal" data-target="#validationPreviewModal">
            <i class="fa fa-check-square-o" aria-hidden="true"></i> ETR Validation
        </a>
    @endif


    <div class="col-6 ml-auto mr-auto">
        <!-- Edit -->
        <a href="{{ route('biller.invoices.edit_project_invoice', $invoice) }}" class="btn btn-warning mb-1"><i class="fa fa-pencil"></i> Edit</a>            
        <a href="#cancel-invoice-modal" class="btn btn-danger mb-1" data-toggle="modal" data-remote="false"><i class="fa fa-minus-circle"> </i> {{trans('general.cancel')}}</a>
        
        <div class="btn-group">
            <button type="button" class="btn btn-vimeo mb-1 btn-md dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-print"></i> {{trans('general.print')}}</button>
            <div class="dropdown-menu">
                <a class="dropdown-item" target="_blank" href="{{$link}}">{{trans('general.pdf_print')}}</a>
                {{-- <a class="dropdown-item" href="{{route('biller.print_compact',[$invoice['id'],1,$valid_token,1])}}">{{trans('general.pos_print')}}</a> --}}
            </div>
        </div>

        <a href="#" class="btn btn-primary mb-1" data-toggle="modal" data-target="#sendInvoiceModal">
            <i class="fa fa-paper-plane-o"></i> Send SMS & Email
        </a>

        {{-- @if($invoice['i_class'] > 1)
            <a href="#pop_model_4" data-toggle="modal" data-remote="false" class="btn btn-large btn-blue-grey mb-1" title="Change Status"><span class="fa fa-superscript"></span> {{trans('invoices.subscription')}}</a>
        @endif --}}
    </div>
</div>
@if ($invoice->is_cancelled)
    <div class="row">
        <div class="col-1 ml-auto mr-2">
            <div class="badge text-center white d-block m-1">
                <span class="bg-danger round p-1">Cancelled</span>
            </div>
        </div>
    </div>
@endif
