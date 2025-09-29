<!-- cancel -->
<div id="validationPreviewModal" data-id="{{ $creditnote->id }}" class="modal fade">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Credit Note</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-1">
                    @php 
                        $business = auth()->user()->business; 
                        $clientName = @$creditnote->customer->company ?: @$creditnote->customer->name;
                    @endphp
                    <div class="col-md-4">
                        <fieldset class="border p-0 pl-1 pr-1">
                            <legend class="w-auto float-none h5">Basic Info</legend>
                            <h5>
                                <b>Issue Date: </b> {{ dateFormat($creditnote->date) }}<br>
                                <b>Operator: </b> {{ @$creditnote->user->full_name }}<br>
                                <b>Original Invoice Id: </b> {{ @$creditnote->invoice->efris_invoice_id }}<br>
                                <b>Original Invoice No: </b> {{ @$creditnote->invoice->efris_invoice_no }}<br>
                            </h5>
                        </fieldset>
                    </div>
                    <div class="col-md-4">
                        <fieldset class="border p-0 pl-1 pr-1">
                            <legend class="w-auto float-none h5">Customer Details</legend>
                            <h5>
                                <b>Buyer Type: </b> {{ @$creditnote->customer->efris_buyer_type_name }}<br>
                                <b>Buyer Tin: </b> {{ @$creditnote->customer->taxid }}<br>
                                <b>Buyer Legal Name: </b> {{ @$creditnote->customer->company ?: @$creditnote->customer->name }}<br><br>
                            </h5>
                        </fieldset>
                    </div>
                </div>
                
                <fieldset class="border p-0 pl-1 pr-1 mb-1">
                    <legend class="w-auto float-none h5">Goods Details</legend>
                    <div class="table-responsive">
                        <table class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%" style="max-height: 550px; overflow-y: auto;">
                            <thead>
                                <tr style="background: #F6F9FD">
                                    <th>Goods Category Id</th>
                                    <th>Item</th>
                                    <th>Item Code</th>
                                    <th>Quantity</th>
                                    <th>Measure Unit</th>
                                    <th>Unit Price</th>
                                    <th>Tax Rate</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                    <th>Order Num.</th>
                                    <th>Discount Flag</th>
                                    <th>Excise Flag</th>
                                    <th>Deemed Flag</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $grossTotal = 0;
                                    $taxTotal = 0;
                                    $netTotal = 0;
                                    $taxableTotal = 0;
                                    $creditnoteItems = $creditnote->items()
                                        ->where(function($q) {
                                            $q->whereHas('productvar', fn($q) => $q->whereHas('product')->whereHas('efris_good'))
                                            ->orWhereHas('invoice_item', function($q) {
                                                $q->whereHas('product_variation', fn($q) => $q->whereHas('product')->whereHas('efris_good'));
                                            });
                                        })
                                        ->with([
                                            'productvar.product', 'productvar.efris_good', 
                                            'invoice_item.product_variation.product', 'invoice_item.product_variation.efris_good'
                                        ])
                                        ->get();
                                @endphp
                                @foreach ($creditnoteItems as $key => $item)
                                    <tr>
                                        @php
                                            $productVar = $item->productvar ?: $item->invoice_item->product_variation;
                                            $efrisGood = $productVar->efris_good;

                                            $itemQty = -$item->qty;
                                            $taxRate = $item->tax_id * 0.01;
                                            $unitPriceIncl = round($item->rate * (1 + $taxRate), 4);
                                            $total = round($unitPriceIncl * $itemQty, 4);
                                            $tax = round(($total * $taxRate/(1+$taxRate)), 4);
                                            $subtotal = $total-$tax;

                                            $grossTotal += $total;
                                            $taxTotal += $tax;
                                            $netTotal += $subtotal;
                                            $taxableTotal += ($tax? $subtotal : 0);
                                        @endphp
                                        <td>{{ $productVar->efris_commodity_code }}</td>
                                        <td style="min-width: 30rem">{{ $item->name }}</td>
                                        <td>{{ $efrisGood->goods_code }}</td>
                                        <td>{{ $itemQty }}</td>
                                        <td>{{ $efrisGood->measure_unit }}</td>
                                        <td>{{ number_format($unitPriceIncl, 4) }}</td>
                                        <td>{{ $taxRate }}</td>
                                        <td>{{ number_format($tax, 4) }}</td>
                                        <td>{{ number_format($total, 4) }}</td>
                                        <td>{{ $key }}</td>
                                        <td>2:Non-Discount</td>
                                        <td>2:Not-Excise</td>
                                        <td>2:Not-Deemed</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <h5 class="text-right pr-2">Gross Total: <b>{{ number_format($grossTotal, 4) }}</b></h5>
                    </div>
                </fieldset>

                <fieldset class="border p-0 pl-2 pr-2">
                    <legend class="w-auto float-none h5">Tax Details</legend>
                    <div class="table-responsive">
                        <table class="table table-lg table-bordered zero-configuration font-weight-bold" cellspacing="0" width="100%">
                            <thead>
                                <tr style="background: #F6F9FD">
                                    <th>Tax Class</th>
                                    <th>01:A-Standard</th>
                                    <th>02:B-Zero</th>
                                    <th>03:C-Exempt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Tax Exempt -->
                                @if ($creditnote->customer && $creditnote->customer->is_tax_exempt == 1)
                                    <tr>
                                        <td>Taxable Amount</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ number_format($taxableTotal, 4)  }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tax Rate(%)</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ $creditnote->tax_id }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tax Amount</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ number_format($tax, 4) }}</td>
                                    </tr>
                                <!-- Tax Standard -->
                                @elseif ($creditnote->tax_id)
                                    <tr>
                                        <td>Taxable Amount</td>
                                        <td>{{ number_format($taxableTotal, 4)  }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Rate(%)</td>
                                        <td>{{ $creditnote->tax_id }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Amount</td>
                                        <td>{{ number_format($taxTotal, 4) }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                <!-- Zero Rated -->
                                @elseif ($creditnote->tax_id == 0)
                                    <tr>
                                        <td>Taxable Amount</td>
                                        <td></td>
                                        <td>{{ number_format($taxableTotal, 4)  }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Rate(%)</td>
                                        <td></td>
                                        <td>{{ $creditnote->tax_id }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Tax Amount</td>
                                        <td></td>
                                        <td>{{ number_format($tax, 4) }}</td>
                                        <td></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </fieldset>

                <div class="modal-footer">                        
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{trans('general.close')}}</button>
                    <button type="button" id="confirmInvoiceBtn" class="btn btn-vimeo"><i class="fa fa-exclamation-circle"></i> Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>
