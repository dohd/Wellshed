<div class='form-group row'>
    <div class="col-4">
     <label for="supject">Subject</label>
     <input type="text" name="subject" id="subject" value="{{$rfq_analysis->subject}}" class="form-control">
     <input type="hidden" name="rfq_id" id="rfq_id" value="{{$rfq_analysis->rfq_id}}" class="form-control">
    </div>
    <div class="col-4">
     <label for="">Date</label>
     <input type="text" name="date" id="date" class="form-control datepicker">
    </div>
    <div class="col-4">
        <label for="">Select Supplier</label>
        <select name="supplier_id" id="supplier_id" class="form-control" data-placeholder="Search Supplier" disabled>
            <option value="">Search Supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{$supplier->id}}" {{$supplier->id == @$rfq_analysis->supplier_id ? 'selected' : ''}}>{{$supplier->company ?: $supplier->name}}</option>
            @endforeach
        </select>
       </div>
 </div>
 
 <div class="form-group row table-responsive height-1000">
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
                    {{-- <td><input type="text" name="availability_details[]" id="availability_details" value="{{ $item->availability_details }}" class="form-control"></td>
                    <td><input type="text" name="credit_terms[]" value="{{ $item->credit_terms }}" id="credit_terms" class="form-control"></td>
                    <td><input type="text" name="comment[]" id="comment" value="{{ $item->comment }}" class="form-control"></td> --}}
                    <td>{{ $item->product ? fifoCost($item->product->id) : 0}}</td>

                    <input type="hidden" name="rfq_analysis_item_id[]" value="{{ $item->id }}">
                    <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                    <input type="hidden" name="rfq_item_id[]" value="{{ $item->rfq_item_id }}">

                    @foreach ($suppliers as $supplier)
                        @php
                            $supplierItem = $rfq_analysis->supplier_items->where('supplier_id', $supplier->id)->where('rfq_item_id', $item->rfq_item_id)->first();
                        @endphp
                        <td>
                            <input type="hidden" name="supplier[{{ $supplier->id }}][{{ $item->product_id }}][id][]" id="id" value="{{ $supplierItem->id ?? '' }}">
                            <input type="hidden" name="supplier[{{ $supplier->id }}][{{ $item->product_id }}][rfq_item_id][]" value="{{ $supplierItem->rfq_item_id ?? '' }}" id="rfq_item_id">
                            <input type="number" step="0.01" name="supplier[{{ $supplier->id }}][{{ $item->product_id }}][price][]" value="{{ $supplierItem->price ?? '' }}" class="form-control price" data-supplier-name="{{$supplier->company}}" 
                            data-item-index="{{$i}}">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="supplier[{{ $supplier->id }}][{{ $item->product_id }}][amount][]" value="{{ $supplierItem->amount ?? '' }}" class="form-control amount" readonly>
                        </td>
                    @endforeach
                    <td>
                        <select class="form-control lowest-supplier" name="supplier_id[]">
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{$supplier->company}}" {{$item->supplier_id == $supplier->id ? 'selected' : ''}}>{{$supplier->company}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
            {{-- <tr>
                <td colspan="4"><input type="text" placeholder="Availability Details" value="{{$rfq_analysis->availability_details}}" name="availability_details" id="availability_details" class="form-control"></td>
                <td colspan="4"><input type="text" placeholder="Credit Terms" value="{{$rfq_analysis->credit_terms}}" name="credit_terms" id="credit_terms" class="form-control"></td>
                <td colspan="4"><input type="text" placeholder="Comments" value="{{$rfq_analysis->comment}}" name="comment" id="comment" class="form-control"></td>
            </tr> --}}
            <tr>
                <td colspan="2"></td>
                <td colspan="4">Avalability of Items (days)</td>
                @foreach ($suppliers as $supplier)
                @php
                    $detail = $rfq_analysis->details->where('supplier_id', $supplier->id)->first();
                @endphp
                <td colspan="2"><input type="text" value="{{@$detail->availability_details}}" name="others[{{$supplier->id}}][availability_details][]" id="availability_details" class="form-control availability_details"></td>
                @endforeach
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="4">Credit Period (days)</td>
                @foreach ($suppliers as $supplier)
                @php
                    $detail = $rfq_analysis->details->where('supplier_id', $supplier->id)->first();
                @endphp
                <td colspan="2"><input type="text" value="{{@$detail->credit_terms}}" name="others[{{$supplier->id}}][credit_terms][]" id="credit_terms" class="form-control credit_terms"></td>
                @endforeach
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="4">General Remarks (days)</td>
                @foreach ($suppliers as $supplier)
                @php
                    $detail = $rfq_analysis->details->where('supplier_id', $supplier->id)->first();
                @endphp
                <td colspan="2"><input type="text" value="{{@$detail->comment}}" name="others[{{$supplier->id}}][comment][]" id="comment" class="form-control comment"></td>
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
                    <select class="form-control winner-supplier" name="winner_supplier_name">
                        <option value="">Select Winner</option>
                    </select>
                </td>
             </tr>
         </tfoot>
     </table>
 </div>
 
 