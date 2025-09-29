<fieldset class="border p-1 mb-3">
    <legend class="w-auto float-none h5">Service Expense</legend>
    <div class="table-responsive" style="max-height: 80vh">                            
        <table id="expensesTbl" class="table tfr my_stripe_single pb-2 text-center">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th>#</th>
                    <th>Milestone</th>
                    <th class="text-left">Expense Item</th>
                    <th>UoM</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($serviceExpenses as $i => $row)
                    <tr>
                        <td><span class="num">{{ $i+1 }}</span></td>
                        <td><span class="milestone">{!! $row->milestone !!}</span></td>
                        <td class="text-left"><span class="descr">{!! $row->description !!}</span></td>
                        <td><span class="uom">{{ $row->uom }}</span></td>   
                        <td><span class="amount">{{ numberFormat($row->amount) }}</span></td>   
                    </tr> 
                @endforeach                                           
            </tbody>
        </table>
    </div>
    <div class="row mt-1">
        <div class="col-3 ml-auto">
            <div class="table-responsive">
                @php
                    $nonMilestoneAmt = $serviceExpenses->whereNull('milestone_id')->sum('amount');
                    $milestoneAmt = $serviceExpenses->whereNotNull('milestone_id')->sum('amount');
                    $servicesTtl = $serviceExpenses->sum('amount');
                @endphp
                <table id="serviceSummaryTbl" class="table table-bordered">
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
                            <td class="pl-1">{{ numberFormat($servicesTtl) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>        
    </div>
</fieldset>