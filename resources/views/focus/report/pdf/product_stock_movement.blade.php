@extends ('focus.report.pdf.statement')

@section('statement_body')
    <small style="font-size:.6em">Movement From <i>{{ $lang['from_date'] }}</i> To <i>{{ $lang['to_date'] }}</i></small>
    @php $product = $account_details->last() @endphp
    <div style="font-size:.7em; text-align:left">
        <b>{{ @$product->name }}</b> <br> 
        {{ @$product->location }}
    </div>
    
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>Date</td>
            <td>Type</td>
            <td>Supplier</td>
            <td>Dnote/Ref No</td>
            <td>Invoice/Quote No</td>
            <td>Project No</td>
            <td>UoM</td>
            <td>Qty</td>
            <td>On Hand</td>
            <td>Avg Cost</td>
            <td>Asset Value</td>
        </tr>
        @if (round($priorQty, 1))
            <tr class="item">
                <td colspan="8"><b>** Quantity Brought Foward **</b></td>
                <td><b>{{ round($priorQty, 1) }}</b></td>
                <td colspan="2"></td>
            </tr>
        @endif
        @foreach ($account_details as $item)
            <tr class="item">
                <td>{{ dateFormat($item->date) }}</td>
                <td>{{ $item->type }}</td>
                <td>{{ $item->supplier }}</td>
                <td>{{ $item->dnote_refno }}</td>
                <td>{{ $item->invoice_quote_no }}</td>
                <td>{{ $item->project_no }}</td>
                <td>{{ $item->uom }}</td>
                <td>{{ round($item->qty, 1) }}</td>
                <td>{{ round($item->qty_onhand, 1) }}</td>
                <td>{{ numberFormat($item->avg_cost) }}</td>
                <td>{{ numberFormat($item->amount) }}</td>
            </tr>
        @endforeach
        <!-- 20 dynamic empty rows -->
        @for ($i = count($account_details); $i < 10; $i++)
            <tr class="item">
                @for($j = 0; $j < 9; $j++)
                    <td></td>
                @endfor
            </tr>
        @endfor
        <!--  -->
    </table>
    <br>
    <div class="subtotal-container" style="width:20%">
        <table class="subtotal">
            <thead></thead>
            <tbody>
                <tr>
                    <td colspan="2" class="summary"><strong>{{trans('general.summary')}}</strong></td>
                </tr>
                <tr>
                    <td>{{trans('general.total')}}:</td>
                    @php $lastStockTr = $account_details->where('amount', '!=', 0)->last() @endphp
                    <td style="text-align:right;">{{ numberFormat(@$lastStockTr->amount) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection