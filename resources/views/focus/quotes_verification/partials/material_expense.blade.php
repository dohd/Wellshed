<fieldset class="border p-1 mb-3">
    <legend class="w-auto float-none h5">Material Expense</legend>
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
                </tr>
            </thead>
            <tbody>
                @foreach ($materialExpenses as $i => $row)
                    <tr>
                        <td><span class="num">{{ $i+1 }}</span></td>
                        <td><span class="milestone">{!! $row->milestone !!}</span></td>
                        <td class="text-left"><span class="descr">{!! $row->product_name !!}</span></td>
                        <td><span class="item-code">{{ $row->product_code }}</span></td>
                        <td><span class="uom">{{ $row->unit }}</span></td>   
                        <td><span class="amount">{{ numberFormat($row->total_expense) }}</span></td>   
                    </tr> 
                @endforeach                                           
            </tbody>
        </table>
    </div>

    <div class="row mt-1">
        <div class="col-3 ml-auto">
            <div class="table-responsive">
                @php
                    $nonMilestoneAmt = $materialExpenses->whereNull('milestone_id')->sum('total_expense');
                    $milestoneAmt = $materialExpenses->whereNotNull('milestone_id')->sum('total_expense');
                    $materialsTtl = $materialExpenses->sum('total_expense');
                @endphp
                <table id="materialSummaryTbl" class="table table-bordered">
                    <tbody>
                        <tr>
                            <th width="50%">Non-Milestone</th>
                            <td class="pl-1">{{ numberFormat($nonMilestoneAmt) }}</td>
                        </tr>
                        <tr>
                            <th width="50%">Milestone</th>
                            <td class="pl-1">{{ numberFormat($milestoneAmt) }}</td>
                        </tr>
                        <tr>
                            <th width="50%">Total</th>
                            <td class="pl-1">{{ numberFormat($materialsTtl) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>        
    </div>
</fieldset>