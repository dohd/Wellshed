@extends ('focus.report.pdf.statement')
@section('statement_body')
    <small style="font-size:.6em">Movement From {{ $lang['from_date'] }} To {{ $lang['to_date'] }}</small>
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>{{trans('general.date')}}</td>
            <td>{{trans('products.product')}}</td>
            <td>From Location</td>
            <td>Qty Out</td>
            <td>To Destination</td>
            <td>Qty In</td>
            <td>On Hand</td>
            <td>Asset Value</td>
        </tr>
        @foreach ($account_details as $item)
            <tr class="item">
                <td>{{ $item->stock_transfer? dateFormat($item->stock_transfer->date) : '' }}</td>
                <td>{{ @$item->productvar->name }}</td>
                <td>{{ @$item->stock_transfer->source->title }}</td>
                <td>{{ +$item->qty_transf }}</td>
                <td>{{ @$item->stock_transfer->destination->title }}</td>
                <td>{{ +$item->qty_rcv }}</td>
                <td>{{ +$item->qty_onhand }}</td>
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