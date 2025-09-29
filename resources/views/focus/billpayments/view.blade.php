@extends ('core.layouts.app')

@section('title', 'Bill Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Bill Payment Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.billpayments.partials.billpayments-header-buttons')
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $pmt = $billpayment;
                            $details = [
                                'Payment No' => $pmt->tid,
                                'Supplier' => $pmt->supplier ? $pmt->supplier->name : '',
                                'Date' => dateFormat($pmt->date),
                                'Amount' => numberFormat($pmt->amount),
                                'Allocated Amount' => numberFormat($pmt->allocate_ttl),
                                'Payment Mode' => $pmt->payment_mode,
                                'Reference' => $pmt->reference,
                                'Payment Account' => $pmt->account? $pmt->account->holder : '',
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
                        <table class="table tfr my_stripe_single text-center" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Bill No</th>
                                    <th>Supplier Name</th>
                                    <th>Note</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($pmt->items as $i => $item)
                                    @if ($bill = $item->supplier_bill)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ dateFormat($bill->due_date) }}</td>
                                            <td>{{ gen4tid('BILL-', $bill->tid) }}</td>
                                            <td>{{ $bill->purchase? $bill->purchase->suppliername : $pmt->supplier->name }}</td>
                                            <td>{{ $bill->note }}</td>
                                            <td>{{ $bill->status }}</td>
                                            <td>{{ numberFormat($bill->total) }}</td>
                                            <td>{{ numberFormat($bill->amount_paid) }}</td>
                                            <td>{{ numberFormat($bill->total - $bill->amount_paid) }}</td>    
                                        </tr>
                                    @endif
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
