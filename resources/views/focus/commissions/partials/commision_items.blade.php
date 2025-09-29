 <table id="budgetsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Commission Type</th>
            <th>Commision</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_commision = 0;
        @endphp
        @if (!empty($commission->items))
           @foreach ($commission->items as $k => $item)
            <tr>
                @php
                    $actual_commission = $item['actual_commission'];
                    $total_commision += $actual_commission;
                @endphp

                <td>{{ $k+1 }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['phone'] }}</td>
                <td>{{ $item['commission_type'] }}</td>
                <td><input type="text" name="raw_commision[]" class="form-control" value="{{ $item['raw_commision'] }}" id="" readonly></td>
                <td> <input type="text" class="form-control" name="actual_commission[]" value="{{ $item['actual_commission'] }}" id="" readonly></td>
                <input type="hidden" name="reserve_uuid[]" value="{{ $item['uuid'] }}" id="">
                <input type="hidden" name="commission_type[]" value="{{ $item['commision_type'] }}" id="">
                <input type="hidden" name="invoice_id[]" value="{{ $item['invoice_id'] }}" id="">
                <input type="hidden" name="invoice_amount[]" value="{{ $item['total'] }}" id="">
                <input type="hidden" name="quote_id[]" value="{{ $item['quote_id'] }}" id="">
                <input type="hidden" name="quote_amount[]" value="{{ $item['quote_amount'] }}" id="">
                <input type="hidden" name="name[]" value="{{ $item['name'] }}" id="">
                <input type="hidden" name="phone[]" value="{{ $item['phone'] }}" id="">
                <input type="hidden" name="customer_enrollment_item_id[]" value="{{ $item['customer_enrollment_item_id'] }}" id="">
                <input type="hidden" name="id[]" value="{{ $item['id'] }}" id="">
            </tr>
        @endforeach 
        @else
           @foreach ($reserves as $k => $item)
                <tr>
                    @php
                        $actual_commission = $item['actual_commission'];
                        $total_commision += $actual_commission;
                    @endphp

                    <td>{{ $k+1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['phone'] }}</td>
                    <td>{{ $item['commision_type'] }}</td>
                    <td><input type="text" name="raw_commision[]" class="form-control" value="{{ $item['raw_commision'] }}" id="" readonly></td>
                    <td><input type="text" class="form-control" name="actual_commission[]" value="{{ $actual_commission }}" id="" readonly></td>
                    <input type="hidden" name="reserve_uuid[]" value="{{ $item['uuid'] }}" id="">
                    <input type="hidden" name="commission_type[]" value="{{ $item['commision_type'] }}" id="">
                    <input type="hidden" name="invoice_id[]" value="{{ $item['invoice_id'] }}" id="">
                    <input type="hidden" name="invoice_amount[]" value="{{ $item['total'] }}" id="">
                    <input type="hidden" name="name[]" value="{{ $item['name'] }}" id="">
                    <input type="hidden" name="phone[]" value="{{ $item['phone'] }}" id="">
                    <input type="hidden" name="quote_id[]" value="{{ $item['quote_id'] }}" id="">
                    <input type="hidden" name="quote_amount[]" value="{{ $item['quote_amount'] }}" id="">
                    <input type="hidden" name="customer_enrollment_item_id[]" value="{{ $item['customer_enrollment_item_id'] }}" id="">
                </tr>
            @endforeach 
        @endif
        
        <tfoot>
            <tr>
                <td>Total Commission</td>
                <td colspan="4"></td>
                <td><input type="text" name="total" class="form-control" value="{{ numberFormat($total_commision) }}" id="" readonly></td>
            </tr>
        </tfoot>
    </tbody>
</table>