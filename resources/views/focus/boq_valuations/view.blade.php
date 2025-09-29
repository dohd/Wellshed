@extends ('core.layouts.app')
@section('title', 'BoQ Valuation')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">BoQ Valuation</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.boq_valuations.partials.boq_valuations-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <table class="table table-bordered table-sm">
                                @php
                                    $bv = $boq_valuation;
                                    $boq = $bv->boq;
                                    $lead = optional($boq_valuation->boq->lead);
                                    $customer = optional($lead->customer);
                                    $branch = optional($lead->branch);
                                
                                    $name = $customer->company ?: $customer->name;
                                    $name ? $name . ($branch->name ? " - {$branch->name}" : '') : '';
                                    
                                    $details = [
                                        'Customer' => $name,
                                        '#Serial' => gen4tid('BV-', $bv->tid),
                                        '#BoQ No' => $boq? gen4tid('BoQ-', $boq->tid) : '',
                                        'Valuation Date' => dateFormat($bv->date),
                                        'Order Amount' => numberFormat($bv->subtotal),
                                        '% Valued' => +$bv->valued_perc,
                                        'Valuated Amount' => numberFormat($bv->valued_subtotal),
                                        'Valuation Balance' => numberFormat($bv->balance),
                                        '% Retention' => +$bv->perc_retention,
                                        'Retention Amount' => numberFormat($bv->retention),
                                        'Retention Note' => $bv->retention_note,                                        
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="50%">{{ $key }}</th>
                                        <td class="pl-1">{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            <!-- Documents -->
                            <table id="docTbl" class="table table-bordered" width="50%">
                                <thead>
                                    <tr class="text-center">
                                        <th width="50%">File Caption</th>
                                        <th>Interim Document / Certificate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bv->docs as $row)
                                        <tr class="text-center">
                                            <td>{{ $row->caption }}</td>
                                            <td>
                                                @if($row->document_name)
                                                    <p>
                                                        <a href="{{ Storage::disk('public')->url('files/valuation_cert/' . $row->document_name) }}" target="_blank">
                                                            {{ $row->document_name }}
                                                        </a>
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-6">
                            @php 
                                $details = [
                                    'Expense Total' => numberFormat($bv->exp_total),
                                    '% Valued' => +$bv->exp_valuated_perc,
                                    'Expense valuated' => numberFormat($bv->exp_valuated),
                                    'Expense Balance' => numberFormat($bv->exp_balance),
                                ];
                            @endphp
                            <table class="table table-bordered table-sm">
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="50%">{{ $key }}</th>
                                        <td class="pl-1">{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            <br>
                            @php 
                                $employee_ids = explode(',', $bv->employee_ids);
                                $employees = App\Models\hrm\Hrm::whereIn('id', $employee_ids)->get()
                                ->map(fn($v) => $v->full_name)
                                ->implode(', ');
                                
                                $details = [
                                    'Completion Date' => $bv->completion_date ? dateFormat($bv->completion_date) : '',
                                    'DLP Period (In Months)' => $bv->dlp_period > 0 ? numberFormat($bv->dlp_period) : '',
                                    'DLP Reminder (In Days)' => $bv->dlp_reminder > 0 ? numberFormat($bv->dlp_reminder) : '',
                                    'User To Notify' => $employees,
                                ];
                            @endphp
                            <table class="table table-bordered table-sm">
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="50%">{{ $key }}</th>
                                        <td class="pl-1">{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- jobcards / dnotes -->
        @if ($bv->job_cards->count())
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 80vh">
                            <table id="jobcardsTbl" class="table pb-2 tfr text-center">
                                <thead class="bg-gradient-directional-blue white pb-1">
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th>Item Type</th>
                                        <th>Ref No</th>                                                    
                                        <th>Date</th>
                                        <th>Technician</th>
                                        <th>Equipment</th>
                                        <th>Location</th>
                                        <th>Fault</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bv->job_cards as $item)
                                        <tr>
                                            <td>{{ $item->type == 1? 'JOBCARD' : 'DNOTE' }}</td>
                                            <td>{{ $item->reference }}</td>
                                            <td>{{ dateFormat($item->date) }}</td>
                                            <td>{{ $item->technician }}</td>
                                            <td>{{ @$item->equipment->capacity }} {{ @$item->equipment->make_type }}</textarea>
                                            <td>{{ @$item->location }}</td>
                                            <td>{{ $item->fault }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>        
        @endif

        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <!-- Order Items -->
                    <fieldset class="border p-1 mb-3">
                        <legend class="w-auto float-none h5">Order Items</legend>
                        <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
                            <table id="productsTbl" class="table tfr my_stripe_single pb-2 text-center">
                                <thead>
                                    <tr class="item_header bg-gradient-directional-blue white">
                                        <th>#</th>
                                        <th>Item Description</th>
                                        <th>UoM</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                        <th width="15%">VAT</th>
                                        <th width="5%">% Valued</th>
                                        <th width="5%">Amount Valued</th>
                                        <th width="5%">Value Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bv->items as $item)
                                        @if ($item->row_type == 1)
                                            <tr>
                                                <td>{{ $item->numbering }}</td>
                                                <td style="text-align: left;">{{ $item->product_name }}</td>
                                                <td>{{ $item->unit }}</td>
                                                <td>{{ +$item->product_qty }}</td>
                                                <td>{{ numberFormat($item->product_subtotal) }}</td>
                                                <td>{{ numberFormat($item->product_amount) }}</td>
                                                <td>{{ numberFormat($item->product_tax) }} ({{ +$item->tax_rate }}%)</td>
                                                <td>{{ +$item->perc_valuated }}</td>
                                                <td>{{ numberFormat($item->total_valuated) }}</td>
                                                <td>{{ numberFormat($item->product_valued_bal) }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td class="font-weight-bold">{{ $item->numbering }}</td>
                                                <td class="font-weight-bold">{{ $item->product_name }}</td>
                                                <td colspan="7"></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>                        
                    </fieldset>

                    <!-- Material Expense -->
                    @php 
                        $materialItems = $bv->valuatedExps()
                        ->whereNotIn('category', ['dir_purchase_service', 'labour_service'])
                        ->get();  
                    @endphp
                    @if ($materialItems->count())
                        <fieldset class="border p-1 mb-3">
                            <legend class="w-auto float-none h5">Material Expense</legend>
                            <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
                                <table id="materialsTbl" class="table tfr my_stripe_single pb-2 text-center">
                                    <thead>
                                        <tr class="item_header bg-gradient-directional-blue white">
                                            <th>#</th>
                                            <th>Milestone</th>
                                            <th>Item Description</th>
                                            <th>UoM</th>
                                            <th>Amount</th>
                                            <th width="5%">% Valued</th>
                                            <th width="5%">Amount Valued</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($materialItems as $i => $row)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ @$row->milestone->name }}</td>
                                                <td>{{ $row->product_name }}</td>
                                                <td>{{ $row->uom }}</td>
                                                <td><span class="amount">{{ numberFormat($row->amount)  }}</span></td>   
                                                <td>{{ +$row->perc_valuated }}</td>                
                                                <td>{{ numberFormat($row->total_valuated) }}</td>
                                            </tr>                            
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    @endif

                    <!-- Service Expense -->
                    @php 
                        $serviceItems = $bv->valuatedExps()
                        ->whereIn('category', ['dir_purchase_service', 'labour_service'])
                        ->get();
                    @endphp
                    @if ($serviceItems->count())
                        <fieldset class="border p-1 mb-3">
                            <legend class="w-auto float-none h5">Service Expense</legend>
                            <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
                                <table id="materialsTbl" class="table tfr my_stripe_single pb-2 text-center">
                                    <thead>
                                        <tr class="item_header bg-gradient-directional-blue white">
                                            <th>#</th>
                                            <th>Milestone</th>
                                            <th>Item Description</th>
                                            <th>UoM</th>
                                            <th>Amount</th>
                                            <th width="5%">% Valued</th>
                                            <th width="5%">Amount Valued</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($serviceItems as $i => $row)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ @$row->milestone->name }}</td>
                                                <td>{{ $row->product_name }}</td>
                                                <td>{{ $row->uom }}</td>
                                                <td><span class="amount">{{ numberFormat($row->amount)  }}</span></td>   
                                                <td>{{ +$row->perc_valuated }}</td>                
                                                <td>{{ numberFormat($row->total_valuated) }}</td>
                                            </tr>                            
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});
</script>
@endsection
