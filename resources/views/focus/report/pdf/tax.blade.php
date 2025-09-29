@extends ('focus.report.pdf.statement')
@section('statement_body')
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>Pin</td>
            <td>{{$lang['party_2']}}</td>
            <td>{{trans('general.date')}}</td>
            <td>Reference#</td>
            <td>Description</td>
            <td>Taxable Amount</td>-->
            <td>{{trans('general.tax')}}</td>
        </tr>
        @php
            $fill = false;
            $balance=0;
            foreach ($account_details as $row) {
                if ($fill == true) {
                    $flag = ' mfill';
                } else {
                    $flag = '';
                }

                if($row['user_type'] =='supplier'){

                  if($lang['type']==2){

                        $payer= $row->supplier ? $row->supplier->name : '';
                        $pin= $row->supplier ? $row->supplier->taxid : '';
                        if($row['supplier_id'] == 1){
                            $pin = "";
                        }
                
                        $amount=$row['debit'];
                    }
                    
                }else{
                    $payer = $row->customer ? $row->customer->company : '';
                    $pin = $row->customer ? $row->customer->taxid : '';
                
                    $amount=$row['credit'];
                    
                }
                // $payer=$row['payer'];
                // $pin=$row['taxid'];


                if($lang['type']==2){
                    $amount=$row['debit'];
                    $balance += $row['debit'];
                }else{
                    $amount=$row['credit'];
                    $balance += $row['credit'];
                }


                 
                echo '<tr class="item' . $flag . '"><td>' . $pin . '</td><td>' . $payer . '</td><td>' . dateFormat($row['transaction_date']) . '</td><td>' . $row->refer_no. '</td><td>' . $row->note . '</td><td>' . amountFormat($row['taxable_amount']) . '</td><td>' . amountFormat($amount) . '</td></tr>';
                $fill = !$fill;
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
            <td>Total:</td>
            <td>{{amountFormat($balance)}}</td>
        </tr>

        </tbody>
    </table>
@endsection