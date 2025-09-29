@extends ('focus.summary.summary_main')
@section('summary_body')
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>{{trans('general.date')}}</td>
            <td>{{trans('products.product')}}</td>
            <td>{{trans('products.price')}}</td>
            <td>{{trans('products.qty')}}</td>
            <td>{{trans('general.total')}}</td>
        </tr>
       @php
            $total = 0;
            foreach ($sale_items as $row) {
                $amount = 0;
                if($row['product_amount'] && $row['product_amount'] > 0){
                    $amount = $row['product_amount'];
                }else {
                    $amount = $row['product_qty'] * $row['product_price'];
                }
                $total += $amount;

                echo '<tr class=""><td>' . dateFormat($row['date']) . '</td><td>' . $row['product_name'] . '</td><td>' . amountFormat($row['product_price']) .'</td><td>' . numberFormat($row['product_qty']) .  '</td><td>' . amountFormat($amount) . '</td></tr>';
            
            }
       @endphp
    </table>
    <br>
    <table class="subtotal">
        <thead>
        <tbody>
        <tr>
            <td class="myco2" rowspan="2"><br>
            </td>
            <td class="summary"><strong>{{trans('general.summary')}}</strong></td>
            <td class="summary"></td>
        </tr>
        <tr>
            <td>Total Amount:</td>
            <td>{{numberFormat($total)}}</td>
        </tr>

        </tbody>
    </table>
@endsection
