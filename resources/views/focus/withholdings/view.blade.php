@extends ('core.layouts.app')

@section ('title', 'WithHolding Tax management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">WithHolding Tax Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.withholdings.partials.withholdings-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <table id="withholdingTbl" class="table table-sm table-bordered mb-2">
                        @php
                            $details = [
                                'Withholding No.' => gen4tid('WH-', $withholding->tid),
                                'Customer' => $withholding->customer->company,
                                'Certificate' => strtoupper($withholding->certificate),
                                'Certificate Date' => dateFormat($withholding->cert_date),
                                'Certificate Serial No.' => $withholding->reference,
                            ];
                        @endphp
                        <tbody>                    
                            @foreach ($details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr> 
                            @endforeach                    
                        </tbody>
                    </table>
                </div>
                <div class="col-6">
                    <table id="withholdingTbl" class="table table-sm table-bordered mb-2">
                        @php
                            $details = [
                                'Payment Date' => dateFormat($withholding->tr_date),
                                'Amount' => numberFormat($withholding->amount),
                                'Total Allocated' => numberFormat($withholding->allocate_ttl),
                                'Receipt Amount' => numberFormat($withholding->receipt_amount),
                            ];
                            if (!$withholding->rel_payment_id) {
                                $details['Posted Amount'] = numberFormat($withholding->amount - $withholding->receipt_amount);
                            }
                        @endphp
                        <tbody>                    
                            @foreach ($details as $key => $val)
                                <tr>
                                    <th>{{ $key }}</th>
                                    <td>{{ $val }}</td>
                                </tr> 
                            @endforeach                    
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="responsive">
                <table class="table table-sm text-center">
                    <thead>
                        <tr class="bg-gradient-directional-blue white">
                            <th>#</th>
                            <th>Date</th>
                            <th>Invoice No</th>
                            <th>Note</th>
                            <th>Amount Allocated</th>
                            <th>Receipt Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withholding->items()->whereHas('invoice')->get() as $i => $item)
                            <tr>
                                <th>{{ $i+1 }}</th>
                                <td>{{ dateFormat($item->invoice->invoicedate) }}</td>
                                <td>{{ $item->invoice->tid }}</td>
                                <td>{{ $item->invoice->notes }}</td>
                                <td>{{ numberFormat($item->paid) }}</td>
                                <td>{{ $item->paid_invoice_item_id? 'pre-receipt' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
