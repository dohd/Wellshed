@extends ('core.layouts.app')

@section('title', 'Goods Receive Note')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Goods Receive Note</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.goodsreceivenotes.partials.goodsreceivenotes-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header pb-0">
                            <div class="btn-group">
                                @if (config('services.efris.base_url'))
                                    <a href="#" class="btn btn-info ml-1" id="stockAdjustmentBtn" data-toggle="modal" data-target="#stockAdjustmentModal">
                                        <i class="fa fa-check-square-o" aria-hidden="true"></i> ETR GRN Stock Adjustment
                                    </a>
                                @endif
                            </div>
                        </div>  
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                @php
                                    $grn = $goodsreceivenote;
                                    $details = [ 
                                        'GRN No' => gen4tid('GRN-', $grn->tid),
                                        'Currency' => @$grn->currency->code . ' / ' . strval(+$grn->fx_curr_rate),
                                        'Supplier' => $grn->supplier? $grn->supplier->name : '',
                                        'Purchase Type' => $grn->purchaseorder? gen4tid('PO-', $grn->purchaseorder->tid) : '',
                                        'Dnote' => $grn->dnote,
                                        'Date' => dateFormat($grn->date),
                                        'Note' => $grn->note,
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="30%">{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            {{-- goods --}}
                            <div class="table-responsive mt-3">
                                <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                                    <thead>
                                        <tr class="bg-gradient-directional-blue white">
                                            <th>#</th>
                                            <th>Product Description</th>
                                            <th>Project/Quote</th>
                                            <th>UoM</th>
                                            <th>Qty Ordered</th>
                                            <th>Qty Received</th>
                                            <th>Qty Due</th>                                            
                                            <th>Payment Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>   
                                        @foreach ($grn->items as $i => $item)
                                            @if ($po_item = $item->purchaseorder_item)
                                                <tr>
                                                    @php
                                                        $project_name = $item->project? gen4tid('Prj-', $item->project->tid) . ' - ' . $item->project->name : '';
                                                        $project = $item->project;
                                                        $paid = '';
                                                        if ($project) {
                                                            $quote = $project->quote;
                                                            if($quote) {
                                                                $invoice = $quote->invoice_product;
                                                                if($invoice) {
                                                                    $paid = $invoice->invoice ? $invoice->invoice->status : '';
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <td>{{ $i+1 }}</td>
                                                    <td>{{ $po_item->description }}</td>
                                                    <td width="20%" style="">{{ $project_name }}</td>
                                                    <td>{{ $po_item->uom }}</td>
                                                    <td>{{ +$po_item->qty }}</td>
                                                    <td>{{ +$po_item->qty_received }}</td>
                                                    <td>
                                                        @php
                                                            $due = $po_item->qty - $po_item->qty_received;
                                                        @endphp
                                                        {{ $due > 0? +$due : 0 }}
                                                    </td>     
                                                    <td><span class="st-{{$paid}}">{{$paid}}</span></td>                                                                                       
                                                </tr>
                                            @endif
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

    <!-- Stock Adjustment Modal -->
    <div id="stockAdjustmentModal" class="modal fade">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">EFRIS Goods Received Stock Adjustment</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <!-- body loaded via ajax-->
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
</div>
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
            $(document).on('click', '#submitStockAdjustment', View.clickSubmitStockAdjustment);

            $('#stockAdjustmentModal').on('shown.bs.modal', View.onShownModal);
        },

        onShownModal() {
            $.post("{{ route('biller.products.efris_goods_adj_modal') }}", {grn_id: "{{ $grn->id }}"})
            .then(html => {
                $('#stockAdjustmentModal .modal-body').html(html);
            })
            .fail((xhr,status,error) => console.log(error));
        },

        clickSubmitStockAdjustment(e) {
            $('#stockAdjustmentModal').modal('hide');
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
                        url: "{{ route('biller.products.efris_goods_adjustment') }}",
                        form: $("#goodsAdjForm").serialize(),
                    }, true);
                }
            }); 
        },
    };

    $(View.init);
</script>
@endsection
