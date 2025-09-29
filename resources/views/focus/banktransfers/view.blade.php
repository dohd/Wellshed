@extends ('core.layouts.app')
@section ('title', 'Transfer Management | View')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Money Transfer</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.banktransfers.partials.banktransfers-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            @php
                                $receiptAmount = +$banktransfer->receipt_amount;
                                // if (!$receiptAmount) {
                                //     if ($currency) {
                                //         if ($banktransfer->bank_rate == 1) $receiptAmount = $banktransfer->amount;
                                //         elseif ($banktransfer->bank_rate) $receiptAmount = round($banktransfer->amount * $banktransfer->bank_rate, 4);
                                //     }
                                // }

                                $details = [
                                    'Serial No.' => gen4tid('XFER-', $banktransfer->tid),
                                    'Transfer From' => @$banktransfer->source_account->holder,
                                    'Transfer Amount' => amountFormat($banktransfer->amount, @$banktransfer->source_account->currency_id),
                                    'Default Rate' => numberFormat($banktransfer->default_rate),
                                    'Bank Rate' => numberFormat($banktransfer->bank_rate),
                                    'Transfer To' => @$banktransfer->dest_account->holder,
                                    'Receipt Amount' => amountFormat($receiptAmount, @$banktransfer->dest_account->currency_id),
                                    'Date' => dateFormat($banktransfer->transaction_date),
                                    'Mode' => $banktransfer->method,
                                    'Reference No.' => $banktransfer->refer_no,
                                    'Note' => $banktransfer->note,
                                ];
                                if ($banktransfer->default_rate == 1) unset($details['Default Rate'], $details['Bank Rate']);
                            @endphp
                            @foreach($details as $key => $value)
                                <div class="row">
                                    <div class="col-4 pt-1 border-blue-grey border-lighten-5 font-weight-bold">{{ $key }}</div>
                                    <div class="col-6 pt-1 border-blue-grey border-lighten-5">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
