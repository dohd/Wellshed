@extends ('focus.report.pdf.statement')
@section('statement_body')
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>{{trans('general.date')}}</td>
            <td>{{trans('products.product')}}</td>
            <td>{{trans('products.price')}}</td>
            <td>{{trans('products.qty')}}</td>
            <td>{{trans('general.total')}}</td>
        </tr>
        @php
            $fill = false;
            $balance=0;
            
            // dd($lang);
            if($lang['title'] == "Product Location Statement")
            {
                $warehouse_sales = $account_details->map(function ($item) {
                $results = [];
                if ($item->variation) {
                    $results[] = [
                        'id' => $item->id,
                        'product_name' => $item->variation->name,
                        'unit' => $verified_product->unit,
                        'product_qty' => $item->product_qty,
                        'product_price' => $item->product_price,
                        'created_at' => $item->created_at,
                    ];
                }
                if ($item->quote && $item->quote->verified_products) {
                    $item->quote->verified_products->each(function ($verified_product) use (&$results, $item) {
                        if ($verified_product->product_variation) {
                            $results[] = [
                                'id' => $item->id,
                                'product_name' => $verified_product->product_variation->name,
                                'unit' => $verified_product->unit,
                                'product_qty' => $verified_product->product_qty,
                                'product_price' => $verified_product->product_price,
                                'created_at' => $verified_product->created_at,
                            ];
                        }
                    });
                }
                return $results;
            })->flatten(1)->filter()->all();
            // dd($warehouse_sales);
            if($warehouse_sales){
                    foreach ($warehouse_sales as $row) {

                        if ($fill == true) {
                            $flag = ' mfill';
                        } else {
                            $flag = '';
                        }
                        // dd($row);
                        // $balance += $row['product_qty'];
                        echo '<tr class="item' . $flag . '"><td>' . dateFormat($row['created_at']) . '</td><td>' . $row['product_name'] . '</td><td>' . amountFormat($row['product_price']) .'</td><td>' . numberFormat($row['product_qty']) . ' '. $row['unit'] . '</td><td>' . amountFormat($row['product_price']) . '</td></tr>';
                        $fill = !$fill;
                    }
                }else {
                    foreach ($account_details as $row) {

                        if ($fill == true) {
                            $flag = ' mfill';
                        } else {
                            $flag = '';
                        }
                        // dd($row);
                        // $balance += $row['product_qty'];
                        echo '<tr class="item' . $flag . '"><td>' . dateFormat($row['created_at']) . '</td><td>' . $row['product_name'] . '</td><td>' . amountFormat($row['product_price']) .'</td><td>' . numberFormat($row['product_qty']) . ' '. $row['unit'] . '</td><td>' . amountFormat($row['amount']) . '</td></tr>';
                        $fill = !$fill;
                    }
                }
            }else if($lang['title'] == "Product Category Statement"){
                $sold_quantities = $account_details->map(function ($item) {
                $results = [];
                if ($item->variation && $item->variation->product) {
                    $results[] = [
                        'id' => $item->id,
                        'product_name' => $item->variation->product->name,
                        'unit' => $verified_product->unit,
                        'product_qty' => $item->product_qty,
                        'product_price' => $item->product_price,
                        'created_at' => $item->created_at,
                    ];
                }
                if ($item->quote && $item->quote->verified_products) {
                    $item->quote->verified_products->each(function ($verified_product) use (&$results, $item) {
                        if ($verified_product->product_variation && $verified_product->product_variation->product) {
                            $results[] = [
                                'id' => $item->id,
                                'product_name' => $verified_product->product_variation->product->name,
                                'unit' => $verified_product->unit,
                                'product_qty' => $verified_product->product_qty,
                                'product_price' => $verified_product->product_price,
                                'created_at' => $verified_product->created_at,
                            ];
                        }
                    });
                }
                return $results;
            })->flatten(1)->filter()->all();
                if($sold_quantities){
                    foreach ($sold_quantities as $row) {

                        if ($fill == true) {
                            $flag = ' mfill';
                        } else {
                            $flag = '';
                        }
                        // dd($row);
                        // $balance += $row['product_qty'];
                        echo '<tr class="item' . $flag . '"><td>' . dateFormat($row['created_at']) . '</td><td>' . $row['product_name'] . '</td><td>' . amountFormat($row['product_price']) .'</td><td>' . numberFormat($row['product_qty']) . ' '. $row['unit'] . '</td><td>' . amountFormat($row['product_price']) . '</td></tr>';
                        $fill = !$fill;
                    }
                }else {
                    foreach ($account_details as $row) {

                        if ($fill == true) {
                            $flag = ' mfill';
                        } else {
                            $flag = '';
                        }
                        // dd($row);
                        // $balance += $row['product_qty'];
                        echo '<tr class="item' . $flag . '"><td>' . dateFormat($row['created_at']) . '</td><td>' . $row['product_name'] . '</td><td>' . amountFormat($row['product_price']) .'</td><td>' . numberFormat($row['product_qty']) . ' '. $row['unit'] . '</td><td>' . amountFormat($row['amount']) . '</td></tr>';
                        $fill = !$fill;
                    }
                }
            }else if($lang['title'] == "Expense Statement"){
                foreach ($account_details as $row) {

                    if ($fill == true) {
                        $flag = ' mfill';
                    } else {
                        $flag = '';
                    }
                    echo '<tr class="item' . $flag . '"><td>' . dateFormat($row['created_at']) . '</td><td>' . $row['product_name'] . '</td><td>' . amountFormat($row['product_price']) .'</td><td>' . numberFormat($row['product_qty']) . ' '. $row['unit'] . '</td><td>' . amountFormat($row['amount']) . '</td></tr>';
                    $fill = !$fill;
                }
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
            <td>{{trans('accounts.balance')}}:</td>
            <td>{{numberFormat($balance)}}</td>
        </tr>

        </tbody>
    </table>
@endsection
