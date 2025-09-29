@extends ('focus.report.pdf.statement')
@section('statement_body')
    <small style="font-size:.6em">Movement From {{ $lang['from_date'] }} To {{ $lang['to_date'] }}</small>
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>Location</td>
            <td>Product</td>
            <td>Opening Qty</td>
            <td>Qty In</td>
            <td>Qty Out</td>
            <td>Qty On-Hand</td>
            <td>Avg Cost</td>
            <td>Asset Value</td>
        </tr>
        @foreach ($account_details as $item)
            <tr class="item">
                <td width="20%">{{ @$item->warehouse->title }}</td>
                <td width="20%">{{ $item->name }}</td>
                <td>{{ +$item->op_stock_qty }}</td>
                <td>{{ +$item->qty_in }}</td>
                <td>{{ +$item->qty_out }}</td>
                <td>{{ +$item->qty_onhand }}</td>
                <td>{{ numberFormat($item->avg_cost) }}</td>
                <td>{{ numberFormat($item->amount) }}</td>
            </tr>
        @endforeach
        <!-- 20 dynamic empty rows -->
        @for ($i = count($account_details); $i < 10; $i++)
            <tr class="item">
                @for($j = 0; $j < 5; $j++)
                    <td></td>
                @endfor
            </tr>
        @endfor
        <!--  -->
    </table>
    <br>
    <div class="subtotal-container">
        <table class="subtotal">
            <thead></thead>
            <tbody>
                <tr>
                    <td colspan="2" class="summary"><strong>{{trans('general.summary')}}</strong></td>
                </tr>
                <tr>
                    <td>{{trans('general.total')}}:</td>
                    <td style="text-align: right;">{{numberFormat($account_details->sum('amount'))}}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection