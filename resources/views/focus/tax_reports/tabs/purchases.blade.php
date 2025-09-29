<div class="form-group row">
    <div class="col-3 ml-auto">
        <label for="file_status">Action Status</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="purchase_file_all" id="purchase_file_all">
                <label for="purchase_file_all">File All</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="purchase_file_all" id="purchase_remove_all">
                <label class="text-danger" for="purchase_remove_all">Remove All</label>
            </div>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table id="purchaseTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Type</th>
                <th>Pin</th>
                <th>Purchase Date</th>
                <th>Supplier</th>
                <th>Invoice No.</th>
                <th>Description</th>
                <th>Taxable Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @isset($tax_report)
                @php
                    $j = 0;
                @endphp
                @foreach ($tax_report->items as $row)
                    @php
                        $data = [];
                        if ($row->bill) {
                            $bill = $row->bill;
                            $purchase = $bill->document_type == 'direct_purchase'? $bill->purchase : '';
                            $grn = $bill->document_type == 'goods_receive_note'? $bill->grn : '';
                            $note = '';
                            if ($purchase) {
                                if ($bill->tax_rate == 8) $note = gen4tid('DP-', $purchase->tid) . ' Fuel';
                                else $note = gen4tid('DP-', $purchase->tid) . ' Goods';
                            } elseif ($grn) {
                                if ($bill->tax_rate == 8) $note = gen4tid('Grn-', $grn->tid) . ' Fuel';
                                else $note = gen4tid('Grn-', $grn->tid) . ' Goods';
                            }

                            $data = [
                                'id' => $bill->id,
                                'type' => 'purchase',
                                'tax_pin' => $purchase? $purchase->supplier_taxid : $bill->supplier->taxid,
                                'purchase_date' => $bill->date,
                                'supplier' => $purchase? $purchase->suppliername : $bill->supplier->name,
                                'invoice_no' => $bill->reference,
                                'note' => $note,
                                'tax_rate' => $bill->tax_rate,
                                'subtotal' => $bill->subtotal,
                                'total' => $bill->total,
                                'tax' => $bill->tax,
                            ];
                            $j++;
                        } elseif ($row->debit_note) {
                            $dnote = $row->debit_note;
                            $data = [
                                'id' => $dnote->id,
                                'type' => 'debit_note',
                                'tax_pin' => $dnote->supplier->taxid,
                                'purchase_date' => $dnote->date,
                                'supplier' => $dnote->supplier->name,
                                'invoice_no' => $dnote->tid,
                                'note' => 'Debit Note',
                                'tax_rate' => $dnote->tax / $dnote->subtotal * 100,
                                'subtotal' => -1 * $dnote->subtotal,
                                'tax' => -1 * $inv->tax,
                                'total' => -1 * $inv->total,
                            ];
                            $j++;
                        } else continue;
                    @endphp
                    
                    <tr>
                        <td>{{ $j }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $data['type'])) }}</td>
                        <td>{{ $data['tax_pin'] }}</td>
                        <td>{{ dateFormat($data['purchase_date']) }}</td>
                        <td>{{ @$data['supplier'] ? $data['supplier'] : ''  }}</td>
                        <td>{{ $data['invoice_no'] }}</td>
                        <td>{{ $data['note'] }}</td>
                        <td class="subtotal">{{ numberFormat($data['subtotal']) }}</td>
                        <td width="15%">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input purchase-file-row" type="radio" name="radio_p{{ $j }}" {{ $row->is_filed? 'checked=checked' : '' }}>
                                <label for="radio_p{{ $j }}">file</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input purchase-remove-row" type="radio" name="radio_p{{ $j }}" {{ !$row->is_filed? 'checked=checked' : '' }}>
                                <label for="radio_p{{ $j }}" class="text-danger">remove</label>
                            </div>
                        </td>
                        <input type="hidden" class="tax" value="{{ $data['tax'] }}">
                        <input type="hidden" class="total" value="{{ $data['total'] }}">
                        <input type="hidden" class="purchase-id" name="purchase_id[]" value="{{ $data['id'] }}">
                        <input type="hidden"  class="type" name="purchase_type[]" value="{{ $data['type'] }}">
                        <input type="hidden" class="is-filed" name="purchase_is_filed[]" value="{{ $row->is_filed }}">
                        <input type="hidden" class="item-id" name="purchase_item_id[]" value="{{ $row->id }}">
                    </tr>
                @endforeach
            @endisset
        </tbody>                        
    </table>
</div>
<div>
    <div class="ml-auto col-2">
        <div class="label">Total Taxable Amount</div>
        {{ Form::text('purchase_subtotal', null, ['class' => 'form-control', 'id' => 'purchase_subtotal', 'readonly']) }}
    </div>
    <div class="ml-auto col-2">
        <div class="label">Total Tax</div>
        {{ Form::text('purchase_tax', null, ['class' => 'form-control', 'id' => 'purchase_tax', 'readonly']) }}
    </div>
    <div class="ml-auto col-2">
        <div class="label">Total Amount (VAT Inc)</div>
        {{ Form::text('purchase_total', null, ['class' => 'form-control', 'id' => 'purchase_total', 'readonly']) }}
    </div>
</div>