@extends ('core.layouts.app')

@section('title', 'Project Stock Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Stock Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.projectstock.partials.projectstock-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $quote_label = '';
                            $quote = $projectstock->quote;
                            if ($quote) {
                                $tid = gen4tid($quote->bank_id? 'PI-' : 'Qt-', $quote->tid);
                                $quote_label = $tid . ' - ' . $quote->notes;
                            }      
                            $details = [
                                'Issuance No' => gen4tid('ISS-', $projectstock->tid),
                                'Quote / Proforma Invoice' => $quote_label,
                                'Reference' => $projectstock->reference,
                                'Date' => dateFormat($projectstock->date),
                                'Note' => $projectstock->note,
                                'Stock Worth' => numberFormat($projectstock->total),
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="table-responsive">
                        <table class="table tfr my_stripe_single text-center" id="productsTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>UoM</th>
                                    <th>Qty Approved</th>
                                    <th>Warehouse</th>
                                    <th>Qty Issued</th>                                    
                                </tr>
                            </thead>
                            <tbody>   
                            
                                @foreach ($projectstock->items as $i => $item)
                                    @php
                                        $budget_item = $item->budget_item;
                                        $warehouse = $item->warehouse;
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $budget_item->product_name }}</td>
                                        <td>{{ $item->unit }} </td>
                                        <td>{{ +$budget_item->new_qty }}</td>
                                        <td>{{ $warehouse->title }}</td>                                         
                                        <td>{{ +$budget_item->issue_qty }}</td>
                                    </tr>
                                @endforeach
                                
                            </tbody>                
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
