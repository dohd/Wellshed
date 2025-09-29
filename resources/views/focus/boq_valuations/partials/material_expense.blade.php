<fieldset class="border p-1 mb-3">
    <legend class="w-auto float-none h5">Material Expense</legend>
    <div class="row mb-1">
        <div class="col-2">
            {{ Form::text('', null, ['class' => 'form-control', 'id' => 'materialExpPerc', 'placeholder' => '% Value']) }}
        </div>
    </div>
    <div class="table-responsive" style="max-height: 80vh">                            
        <table id="materialsTbl" class="table tfr my_stripe_single pb-2 text-center">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th>#</th>
                    <th>Milestone</th>
                    <th class="text-left">Item Description</th>
                    <th>Item Code</th>
                    <th>UoM</th>
                    <th>Amount</th>
                    <th>Value Balance</th>
                    <th width="5%">% Value</th>
                    <th width="5%">Valued Amount</th>
                </tr>
            </thead>
            <tbody>
                <!-- Product Row Template -->
                @foreach ($materialExpenses as $i => $row)
                    <tr>
                        <td><span class="num">{{ $loop->iteration }}</span></td>
                        <td><span class="milestone">{{ $row->milestone }}</span></td>
                        <td class="text-left"><span class="descr">{!! $row->product_name !!}</span></td>
                        <td><span class="item-code">{{ $row->product_code }}</span></td>
                        <td><span class="uom">{{ $row->uom }}</span></td>   
                        <td><span class="amount">{{ numberFormat($row->amount) }}</span></td>   
                        <td><span class="valued-bal">{{ numberFormat($row->valued_bal) }}</span></td>   
                        <td><input type="text" name="exp_perc_valuated[]" value="" class="form-control perc-val" ></td>                
                        <td><input type="text" name="exp_total_valuated[]" value="" class="form-control amount-val" ></td>
                        <input type="hidden" name="exp_origin_id[]" value="{{ $row->origin_id }}" class="item-id">
                        <input type="hidden" name="exp_category[]" value="{{ $row->exp_category }}" class="categ">
                        <input type="hidden" name="exp_uom[]" value="{{ $row->uom }}" class="uom-inp">
                        <input type="hidden" name="exp_amount[]" value="{{ $row->amount }}" class="amount-inp">
                        <input type="hidden" name="exp_valued_bal[]" value="{{ $row->valued_bal }}" class="valued-bal-inp">
                        <input type="hidden" name="exp_product_name[]" value="{{ $row->description }}" class="descr-inp">
                        <input type="hidden" name="exp_productvar_id[]" value="{{ $row->productvar_id }}" class="prodvar-id">
                        <input type="hidden" name="exp_boq_id[]" value="{{ $boq->id }}" class="quote-id">
                        <input type="hidden" name="exp_project_id[]" value="{{ $row->project_id }}" class="project-id">
                        <input type="hidden" name="exp_budget_item_id[]" value="" class="budget-item-id">
                        <input type="hidden" name="exp_expitem_id[]" value="" class="exp-item-id">
                        <input type="hidden" name="exp_budget_line_id[]" value="{{ $row->milestone_id }}" class="budget-line-id">
                        <input type="hidden" name="exp_casual_remun_id[]" value="" class="casual-remun-id">
                    </tr>  
                @endforeach                           
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-3 ml-auto">
            <div class="table-responsive">
                <table id="materialSummaryTbl" class="table table-bordered">
                    @php
                        $nonMilTotal = $materialExpenses->whereNull('milestone_id')->sum('valued_bal');
                        $milTotal = $materialExpenses->whereNotNull('milestone_id')->sum('valued_bal');
                        $materialTotal = $materialExpenses->sum('valued_bal'); 
                    @endphp
                    <tbody>
                        <tr>
                            <th width="50%">Non-Milestone</th>
                            <td class="pl-1">{{ numberFormat($nonMilTotal) }}</td>
                        </tr>
                        <tr>
                            <th width="50%">Milestone</th>
                            <td class="pl-1">{{ numberFormat($milTotal) }}</td>
                        </tr>
                        <tr>
                            <th width="50%">Total</th>
                            <td class="pl-1">{{ numberFormat($materialTotal) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>        
    </div>
</fieldset>