<div class="form-group row">
    <div class="col-3 ml-auto">
        <label for="file_status">Action Status</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sale_file_all" id="sale_file_all">
                <label for="sale_file_all">File All</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="sale_file_all" id="sale_remove_all">
                <label class="text-danger" for="sale_remove_all">Remove All</label>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="saleTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th>#</th>
                <th>Type</th>
                <th>Pin</th>
                <th>Invoice Date</th>
                <th>Buyer</th>
                <th>Invoice No.</th>
                <th>CU Invoice No.</th>
                <th>Description</th>
                <th>Taxable Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @isset($tax_report)
                @php
                    $i = 0;
                @endphp
                @foreach ($tax_report->items as $row)
                    @php
                        $data = array();
                        if ($row->invoice) {
                            $inv = $row->invoice;
                            $data = [
                                'id' => $inv->id,
                                'type' => 'invoice',
                                'tax_pin' => $inv->customer? $inv->customer->taxid : '',
                                'invoice_date' => $inv->invoicedate,
                                'customer' => $inv->customer? $inv->customer->company : '',
                                'invoice_no' => $inv->tid,
                                'cu_invoice_no' => $inv->cu_invoice_no,
                                'note' => $inv->notes,
                                'subtotal' => $inv->subtotal,
                                'tax' => $inv->tax,
                                'total' => $inv->total,
                            ];
                            $i++;
                        } elseif ($row->credit_note) {
                            $cnote = $row->credit_note;
                            $data = [
                                'id' => $cnote->id,
                                'type' => 'credit_note',
                                'tax_pin' => $cnote->customer? $cnote->customer->taxid : '',
                                'invoice_date' => $cnote->date,
                                'customer' => $cnote->customer? $cnote->customer->company : '',
                                'invoice_no' => $cnote->tid,
                                'cu_invoice_no' => $inv->cu_invoice_no,
                                'note' => 'Credit Note',
                                'subtotal' => -1 * $cnote->subtotal,
                                'tax' => -1 * $inv->tax,
                                'total' => -1 * $inv->total,
                            ];
                            $i++;
                        } else continue;
                    @endphp

                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $data['type'])) }}</td>
                        <td>{{ $data['tax_pin'] }}</td>
                        <td>{{ dateFormat($data['invoice_date']) }}</td>
                        <td>{{ isset($data['customer']) ? $data['customer'] : '' }}</td>
                        <td>{{ $data['invoice_no'] }}</td>
                        <td>{{ $data['cu_invoice_no'] }}</td>
                        <td>{{ $data['note'] }}</td>
                        <td class="subtotal">{{ numberFormat($data['subtotal']) }}</td>
                        <td width="15%">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input sale-file-row" type="radio" name="radio_{{ $i }}" {{ $row->is_filed? 'checked=checked' : '' }}>
                                <label for="radio_{{ $i }}">file</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input sale-remove-row" type="radio" name="radio_{{ $i }}" {{ !$row->is_filed? 'checked=checked' : '' }}>
                                <label for="radio_{{ $i }}" class="text-danger">remove</label>
                            </div>
                        </td>
                        <input type="hidden" class="tax" value="{{ $data['tax'] }}">
                        <input type="hidden" class="total" value="{{ $data['total'] }}">
                        <input type="hidden" class="sale-id" name="sale_id[]" value="{{ $data['id'] }}">
                        <input type="hidden"  class="type" name="sale_type[]" value="{{ $data['type'] }}">
                        <input type="hidden" class="is-filed" name="sale_is_filed[]" value="{{ $row->is_filed }}">
                        <input type="hidden" class="item-id" name="sale_item_id[]" value="{{ $row->id }}">
                    </tr>
                @endforeach
            @endisset
        </tbody>                      
    </table>
</div>

<div>
    <div class="ml-auto col-2">
        <div class="label">Total Taxable Amount</div>
        {{ Form::text('sale_subtotal', null, ['class' => 'form-control', 'id' => 'sale_subtotal', 'readonly']) }}
    </div>
    <div class="ml-auto col-2">
        <div class="label">Total Tax</div>
        {{ Form::text('sale_tax', null, ['class' => 'form-control', 'id' => 'sale_tax', 'readonly']) }}
    </div>
    <div class="ml-auto col-2">
        <div class="label">Total Amount (VAT Inc)</div>
        {{ Form::text('sale_total', null, ['class' => 'form-control', 'id' => 'sale_total', 'readonly']) }}
    </div>
</div>