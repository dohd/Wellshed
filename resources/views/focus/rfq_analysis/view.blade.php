@extends ('core.layouts.app')

@section ('title', 'View RFQ Analysis')

@section('page-header')
    <h1>
        Manage RFQ Analysis
        <small>View RFQ Analysis</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View RFQ Analysis</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.rfq_analysis.partials.rfq_analysis-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#approvalModal">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Status
                                </a>
                                <a href="#" class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#sendSmsEmailModal">
                                    <i class="fa fa-plus" aria-hidden="true"></i> Notify Suppliers
                                </a>
                            </div>

                            <div class="card-content">

                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>RFQ Analysis No.</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{gen4tid('RFQA-',$rfq_analysis['tid'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>RFQ No.</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5 p-1 font-weight-bold">
                                            <a href="{{ route('biller.rfq.show', @$rfq_analysis->rfq['id']) }}" target="_blank">
                                                <p>{{ gen4tid('RFQ-', @$rfq_analysis->rfq['tid']) }}</p>
                                            </a>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Subject</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$rfq_analysis['subject']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{dateFormat($rfq_analysis['date'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ucfirst($rfq_analysis['status'])}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Status Note</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$rfq_analysis['status_note']}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Supplier</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{@$rfq_analysis->supplier['company']}}</p>
                                        </div>
                                    </div>


                                </div>


                            </div>
                        </div>
                        <div class="card-body table-responsive height-1000">
                            <table id="rfqAnalysisTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr class="bg-gradient-directional-blue white">
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Product Code</th>
                                        <th>Unit</th>
                                        <th>Qty</th>
                                        {{-- <th>Availability Details</th>
                                        <th>Credit Terms</th>
                                        <th>Comment</th> --}}
                                        <th>Purchase</th>
                                        @foreach ($suppliers as $supplier)
                                            <th colspan="2">{{$supplier->company}}</th>
                                        @endforeach
                                        <th>Choose Supplier</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   @foreach ($rfq_analysis->items as $i => $item)
                                       <tr>
                                           <td>{{ $i + 1 }}</td>
                                           <td>{{ $item->rfq_item->description ?? 'N/A' }}</td>
                                           <td>{{ $item->product->code ?? '' }}</td>
                                           <td>{{ $item->rfq_item->uom ?? '' }}</td>
                                           <td class="quantity">{{ $item->rfq_item->quantity ?? '' }}</td>
                                           {{-- <td>{{ $item->availability_details }}</td>
                                            <td>{{ $item->credit_terms }}</td>
                                            <td>{{ $item->comment }}</td> --}}
                                            <td>{{ $item->product ? numberFormat(fifoCost($item->product->id)) : 0}}</td>
                       
                                           <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                           <input type="hidden" name="rfq_item_id[]" value="{{ $item->rfq_item_id }}">
                       
                                           @foreach ($suppliers as $supplier)
                                               @php
                                                   $supplierItem = $rfq_analysis->supplier_items->where('supplier_id', $supplier->id)->where('rfq_item_id', $item->rfq_item_id)->first();
                                               @endphp
                                               <td>
                                                   <input type="number" name="supplier[{{ $supplier->id }}][{{ $item->product_id }}][price][]" value="{{ $supplierItem->price ?? '' }}" class="form-control price" readonly>
                                               </td>
                                               <td>
                                                   <input type="number" name="supplier[{{ $supplier->id }}][{{ $item->product_id }}][amount][]" value="{{ $supplierItem->amount ?? '' }}" class="form-control amount" readonly>
                                               </td>
                                           @endforeach
                                           <td>
                                                <select class="form-control lowest-supplier" name="supplier_id[]" disabled>
                                                    <option value="">Select Supplier</option>
                                                    @foreach ($suppliers as $supplier)
                                                        <option value="{{$supplier->company}}" {{$item->supplier_id == $supplier->id ? 'selected' : ''}}>{{$supplier->company}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                       </tr>
                                   @endforeach
                                   <tr>
                                    <td colspan="2"></td>
                                    <td colspan="4">Avalability of Items (days)</td>
                                    @foreach ($suppliers as $supplier)
                                    @php
                                        $detail = $rfq_analysis->details->where('supplier_id', $supplier->id)->first();
                                    @endphp
                                    <td colspan="2">{{@$detail->availability_details}}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="4">Credit Period (days)</td>
                                    @foreach ($suppliers as $supplier)
                                    @php
                                        $detail = $rfq_analysis->details->where('supplier_id', $supplier->id)->first();
                                    @endphp
                                    <td colspan="2">{{@$detail->credit_terms}}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="4">General Remarks (days)</td>
                                    @foreach ($suppliers as $supplier)
                                    @php
                                        $detail = $rfq_analysis->details->where('supplier_id', $supplier->id)->first();
                                    @endphp
                                    <td colspan="2">{{@$detail->comment}}</td>
                                    @endforeach
                                </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="6"><strong>Total:</strong></td>
                                        @foreach ($suppliers as $supplier)
                                            <td colspan="2" class="supplier-total text-center" data-supplier-id="{{$supplier->id}}">0.00</td>
                                        @endforeach
                                        <td>
                                           <select class="form-control winner-supplier" name="winner_supplier_name" disabled>
                                               <option value="">Select Winner</option>
                                           </select>
                                       </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.rfq_analysis.partials.approval-modal')
        @include('focus.rfq_analysis.partials.send_sms')
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        const Form = {
            init(){
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#supplier_id').select2({allowClear: true});
                updateWinnerSupplier();
                function updateWinnerSupplier() {
                    // Count occurrences of each selected supplier
                    const supplierCount = {};
                    $(".lowest-supplier").each(function () {
                        const selectedSupplier = $(this).val();
                        if (selectedSupplier) {
                            supplierCount[selectedSupplier] = (supplierCount[selectedSupplier] || 0) + 1;
                        }
                    });

                    // Get the suppliers that are tied or most frequent
                    const suppliers = Object.keys(supplierCount);
                    let maxCount = 0;
                    const tiedSuppliers = [];

                    suppliers.forEach(supplier => {
                        const count = supplierCount[supplier];
                        if (count > maxCount) {
                            maxCount = count;
                            tiedSuppliers.length = 0; // Clear previous ties
                            tiedSuppliers.push(supplier);
                        } else if (count === maxCount) {
                            tiedSuppliers.push(supplier);
                        }
                    });

                    // Update the winner-supplier select options
                    const $winnerSelect = $(".winner-supplier");
                    $winnerSelect.empty().append('<option value="">Select Winner</option>');

                    // Populate all suppliers if there is a tie or no clear winner
                    const allSuppliers = {!! json_encode($suppliers->pluck('company')) !!};
                    const suppliersToDisplay = tiedSuppliers.length > 1 ? allSuppliers : tiedSuppliers;

                    suppliersToDisplay.forEach(supplier => {
                        const count = supplierCount[supplier] || 0;
                        const displayText = count > 0 ? `${supplier} (${count} times)` : supplier;
                        $winnerSelect.append(`<option value="${supplier}">${displayText}</option>`);
                    });

                    // Auto-select the winner if there is only one clear winner
                    if (tiedSuppliers.length === 1) {
                        $winnerSelect.val(tiedSuppliers[0]);
                    }
                }
                function calculateAmountAndTotal() {
                    let supplierTotals = {};

                    $(".price").each(function() {
                        let $priceInput = $(this);
                        let $amountInput = $priceInput.closest("td").next().find(".amount"); // Find corresponding amount input
                        let quantity = parseFloat($priceInput.closest("tr").find(".quantity").text()) || 0; // Get quantity
                        let price = parseFloat($priceInput.val()) || 0;

                        // Calculate amount
                        let amount = quantity * price;
                        $amountInput.val(amount.toFixed(2));

                        // Extract supplier ID
                        let supplierId = $priceInput.attr("name").match(/\d+/)[0];

                        // Add to supplier total
                        supplierTotals[supplierId] = (supplierTotals[supplierId] || 0) + amount;
                    });

                    // Update total price per supplier
                    $(".supplier-total").each(function() {
                        let supplierId = $(this).attr("data-supplier-id");
                        $(this).text(supplierTotals[supplierId] ? supplierTotals[supplierId].toFixed(2) : "0.00");
                    });

                }
                
                // Trigger calculation on input change
                $(document).on("input", ".price", calculateAmountAndTotal);
                
                // Initial calculation on page load
                calculateAmountAndTotal();
                $('#send_email_sms').change(this.typeChange)
            },
            typeChange(){
                let value = $(this).val();
                if(value == 'sms'){
                    $('.div_subject').addClass('d-none')
                }else{
                    $('.div_subject').removeClass('d-none')
                }
            }
        };
        $(() => Form.init())
    </script>
@endsection