<table id="importsTbl" class="table table-hover" cellspacing="0">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th style="width: 30px;">#</th>
            <th style="width: 180px;">Product Name</th>
            <th style="width: 80px;">Unit</th>
            <th style="width: 200px;">Quantity</th>
            <th style="width: 200px;">Rate (Cost of Item)</th>
            <th style="width: 220px;">Amount</th>
            <th style="width: 90px;">Rate %</th>
            <th style="width: 110px;">Rate By Value</th>
            <th style="width: 200px;">CBM / Weight</th>
            <th style="width: 220px;">Total CBM</th>
            <th style="width: 100px;">% of CBM</th>
            <th style="width: 120px;">Value By CBM</th>
            <th style="width: 150px;">Average (CBM value & Rate Value)</th>
            <th style="width: 130px;">Avg (Rate & Shipping)</th>
            <th style="width: 160px;">Landed Cost /Item</th>
        </tr>
    </thead>
    <tbody>
        
        @isset($import_request)
            @foreach ($import_request->items as $k => $item)
                <tr>
                    <td><span class="numbering">{{$k+1}}</span></td>
                    <td>{{$item->product_name}}</td>
                    <td>{{$item->unit}}</td> 
                    <td><input type="text" name="qty[]" id="qty-p{{$k}}" value="{{$item->qty}}" class="form-control qty"readonly></td>
                    <td><input type="text" name="rate[]" id="rate-p{{$k}}" value="{{$item->rate}}" class="form-control rate"></td>
                    <td><input type="text" name="amount[]" id="amount-p{{$k}}" value="{{$item->amount}}" class="form-control amount" readonly></td>
                    <td><input type="text" name="rate_percent[]" id="rate_percent-p{{$k}}" value="{{$item->rate_percent}}" class="form-control rate_percent" readonly></td>
                    <td><input type="text" name="rate_value[]" id="rate_value-p{{$k}}" value="{{$item->rate_value}}" class="form-control rate_value" readonly></td>
                    <td style="width: 200px;"><input type="text" name="cbm[]" id="cbm-p{{$k}}" value="{{$item->cbm}}" class="form-control cbm"></td>
                    <td><input type="text" name="total_cbm[]" id="total_cbm-p{{$k}}" value="{{$item->total_cbm}}" class="form-control total_cbm" readonly></td>
                    <td><input type="text" name="cbm_percent[]" id="cbm_percent-p{{$k}}" value="{{$item->cbm_percent}}" class="form-control cbm_percent" readonly></td>
                    <td><input type="text" name="cbm_value[]" id="cbm_value-p{{$k}}" value="{{$item->cbm_value}}" class="form-control cbm_value" readonly></td>
                    <td><input type="text" name="avg_cbm_rate_value[]" id="avg_cbm_rate_value-p{{$k}}" value="{{$item->avg_cbm_rate_value}}" class="form-control avg_cbm_rate_value" readonly></td>
                    <td><input type="text" name="avg_rate_shippment[]" id="avg_rate_shippment-p{{$k}}" value="{{$item->avg_rate_shippment}}" class="form-control avg_rate_shippment" readonly></td>
                    <td><input type="text" name="avg_rate_shippment_per_item[]" id="avg_rate_shippment_per_item-p{{$k}}" value="{{$item->avg_rate_shippment_per_item}}" class="form-control avg_rate_shippment_per_item" readonly></td>
                    {{-- <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td> --}}
                    <input type="hidden" name="product_id[]" id="productid-p{{$k}}" value="{{$item->product_id}}">
                    <input type="hidden" name="id[]" class="id" value="{{$item->id}}">
                </tr>
            @endforeach
            <tfoot>
                <tr>
                    <td colspan="3">Totals</td>
                    <td><span class="t_qty">0</span></td>
                    <td><span class="t_rate">0</span></td>
                    <td><span class="t_amount">0</span></td>
                    <td><span class="t_rate_percent">0</span></td>
                    <td><span class="t_rate_value">0</span></td>
                    <td><span class="t_cbm">0</span></td>
                    <td><span class="t_total_cbm">0</span></td>
                    <td><span class="t_cbm_percent">0</span></td>
                    <td><span class="t_cbm_value">0</span></td>
                    <td><span class="t_avg_cbm_rate_value">0</span></td>
                    <td><span class="t_avg_rate_shippment">0</span></td>
                    <td><span class="t_avg_rate_shippment_per_item">0</span></td>
                </tr>
            </tfoot>
        @endisset
    </tbody>
</table>