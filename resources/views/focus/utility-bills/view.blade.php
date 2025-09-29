@extends ('core.layouts.app')

@section('title', 'Bill Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Bill Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.utility-bills.partials.utility-bills-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                @php
                                    $bill = $utility_bill;
                                    $details = [ 
                                        'Bill No' => gen4tid('BILL-', $bill->tid),
                                        'Supplier' => $bill->supplier? $bill->supplier->name : '', 
                                        'Reference' => $bill->reference,
                                        'Bill Document Type' => $bill->document_type,
                                        'Date' => dateFormat($bill->date),
                                        'Due Date' => dateFormat($bill->due_date),
                                        'Note' => $bill->note,
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                @php
                                    $bill = $utility_bill;
                                    $details = [ 
                                        'Currency' => @$bill->currency->code . ' / ' . strval(+$bill->fx_curr_rate),
                                        'Tax %' => +$bill->tax_rate,
                                        'Subtotal' => numberFormat($bill->subtotal),
                                        'Tax' => numberFormat($bill->tax),
                                        'Total' => numberFormat($bill->total),
                                        'Amount Paid' => numberFormat($bill->amount_paid),
                                        'Balance' => numberFormat($bill->total - $bill->amount_paid),
                                        'Status' => ucfirst($bill->status),
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    
                    {{-- bill items --}}
                    <div class="table-responsive mt-3">
                        <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Tax</th>
                                    <th>Amount</th>                                      
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($bill->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $item->note }}</td>
                                        <td>{{ +$item->qty }}</td>
                                        <td>{{ numberFormat($item->subtotal) }}</td>
                                        <td>{{ numberFormat($item->tax) }}</td>
                                        <td>{{ numberFormat($item->total) }}</td>                                  
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
