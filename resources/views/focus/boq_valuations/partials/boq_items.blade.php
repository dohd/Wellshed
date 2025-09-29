<fieldset class="border p-1 mb-3">
    <legend class="w-auto float-none h5">BoQ Items</legend>
    <div class="row mb-1">
        <div class="col-4">
            <select class="custom-select" name="boq_sheet" id="boq_sheet" autocomplete="off">
                <option value="">-- select boq sheet --</option>
                @foreach ($boq_sheets as $item)
                    <option value="{{ $item->id }}">{{ $item->sheet_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
        <table id="boqsTbl" class="table tfr my_stripe_single pb-2 text-center">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>#</th>
                    <th>Item Description</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Rate(Excl)</th>
                    <th>Rate(Incl)</th>
                    <th>Amount</th>
                    <th>Value Balance</th>
                </tr>
            </thead>
            <tbody>
                {{-- @foreach ($boq_items as $i => $v)
                <tr>
                
                    <td><input type="checkbox" id="checkOne" class="checkOne"></td>
                    <td>{{$i+1}}</td>
                    <td>{{$v->description}}</td>
                    <td>{{$v->uom}}</td>
                    <td>{{$v->new_qty}}</td>
                    <td>{{$v->boq_rate}}</td>
                    <td>{{$v->boq_amount}}</td>
                </tr>
                @endforeach --}}
                
            </tbody>
        </table>
    </div>
</fieldset>