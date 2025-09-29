@extends ('core.layouts.app')

@section ('title', trans('labels.backend.invoices.create'))

@section('page-header')
    <h1>
        {{ trans('labels.backend.invoices.management') }}
        <small>{{ trans('labels.backend.invoices.create') }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-body">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <form method="post" id="data_form">
                                <div class="row">
                                    <div class="col-sm-6 cmp-pnl">
                                        <div id="customerpanel" class="inner-cmp-pnl">
                                            <div class="form-group row">
                                                <div class="fcol-sm-12">
 <h3 class="title">{{trans('purchaseorders.bill_from')}} <a href='#' class="btn btn-primary btn-sm round" data-toggle="modal"  data-target="#addCustomer">
                   Add Customer
                                                        </a>
                                                    </h3>
                                                </div>
                                            </div>
                                             <div class="form-group row">

                                                  <div class='col-md-12'>
        <div class='col m-1'>
           
                {{ Form::label( 'method', trans('transactions.payer_type'),['class' => 'col-12 control-label']) }}
                <div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-primary" name="payer_type" id="colorCheck1"
                           value="walkin" checked="">
                    <label class="custom-control-label" for="colorCheck1">Walkin</label>
                </div>

                <!--<div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-purple" name="payer_type" value="supplier" 
                           id="colorCheck3">
                    <label class="custom-control-label" for="colorCheck3">{{trans('suppliers.supplier')}}</label>
                </div>-->

                <div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-success" name="payer_type" value="customer" 
                           id="colorCheck2">
                    <label class="custom-control-label" for="colorCheck2">{{trans('customers.customer')}}</label>
                </div>
                
                <!--<div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-blue-grey" name="payer_type" value="employee"
                           id="colorCheck4">
                    <label class="custom-control-label" for="colorCheck4">{{trans('hrms.employee')}}</label>
                </div>-->
           
        </div>

    </div>

                                             </div>

                                           

    <div class="form-group row">
                                                <div class="frmSearch col-sm-12">
                                                    {{ Form::label( 'cst', trans('purchaseorders.search_supplier'),['class' => 'caption']) }}
                                                    {{ Form::text('cst', null, ['class' => 'form-control round user-box-new', 'placeholder' =>trans('purchaseorders.supplier_search'), 'id'=>'suppliers-box','data-section'=>'suppliers','autocomplete'=>'off','readonly']) }}
                                                    <div id="suppliers-box-result"></div>
                                                </div>
                                            </div>


                                            

                                               <div class="form-group row">
                                                <div class="col-sm-8"><label for="payer"
                                                                             class="caption">Supplire Name*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::text('payer', null, ['class' => 'form-control round required', 'placeholder' => 'Supplier Name','id'=>'payer-name']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-4"><label for="taxid"
                                                                             class="caption">Tax ID</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('taxid', null, ['class' => 'form-control round', 'placeholder' => 'Tax Id','id'=>'taxid']) }}
                                                    </div>
                                                </div>

                                                  {{ Form::hidden('payer_id', '0',['id'=>'payer_id']) }}
                                            </div>

                                            
                                                <div class="form-group row">

                                             
                                                     <div class="col-sm-12">

                                                    <div class="form-group">
                                                        <label for="project"
                                                               class="caption">Ledger Account/Income Account(Credit)*</label>
                                                        <select  name="credit_account_id" class="form-control round required"
                                                                id="credit_account_id">
                                                             <option value="">Select Ledger Account</option>
                                                @foreach($income_accounts as $account)
                                    <option value="{{$account->id}}"> {{$account->holder}}</option>
                                                @endforeach

                                                        </select>
                                                    </div>
                                                </div>


                                                 <div class="col-sm-12">

                                                    <div class="form-group">
                                                        <label for="project"
                                                               class="caption">Ledger Account Asset(Debit)*</label>
                                                        <select  name="debit_account_id" class="form-control round required"
                                                                id="credit_account_id">
                                                             <option value="">Select Ledger Account</option>
                                                @foreach($assert_accounts as $account)
                                    <option value="{{$account->id}}"> {{$account->holder}}</option>
                                                @endforeach

                                                        </select>
                                                    </div>
                                                </div>


                                                 

                                            

</div>

                                           

                                            
                                     
                                        </div>
                                    </div>
                                    <div class="col-sm-6 cmp-pnl">
                                        <div class="inner-cmp-pnl">


                                            <div class="form-group row">

                                                <div class="col-sm-12"><h3
                                                            class="title">{{trans('purchaseorders.properties')}}</h3>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="tid"
                                                                             class="caption">Transaction ID*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::number('tid', @$last_id->tid+1, ['class' => 'form-control round', 'placeholder' => trans('purchaseorders.tid')]) }}
                                                    </div>
                                                </div>

                                                  <div class="col-sm-6"><label for="refer_no"
                                                                             class="caption">{{trans('general.reference')}} Number*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('refer_no', null, ['class' => 'form-control round required', 'placeholder' => trans('general.reference')]) }}
                                                    </div>
                                                </div>
                                             
                                            </div>
                                          
                                            <div class="form-group row">

                                                <div class="col-sm-6"><label for="transaction_date"
                                            class="caption">Invice Date*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                       aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('transaction_date', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="due_date"
                                                    class="caption">Invice Date*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar-o"
                                                       aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::text('due_date', null, ['class' => 'form-control round required', 'placeholder' => trans('invoices.invoicedate'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group row">
                                                <div class="col-sm-6">
                                                    <label for="taxFormat"
                                                           class="caption">{{trans('general.tax')}}*</label>
                                                    <select class="form-control round" name="taxformat" 
                                                            onchange="changeTaxFormat()"
                                                            id="taxFormat">
                                                        @php
                                                            $tax_format='exclusive';
                                                            $tax_format_id=0;
                                                            $tax_format_type='exclusive';
                                                        @endphp
                                                        @foreach($additionals as $additional_tax)

                                                            @php
                                                                if($additional_tax->id == @$defaults[4][0]['feature_value']  && $additional_tax->class == 1){
                                                                 echo '<option value="'.numberFormat($additional_tax->value).'" data-type1="'.$additional_tax->type1.'" data-type2="'.$additional_tax->type2.'" data-type3="'.$additional_tax->type3.'" data-type4="'.$additional_tax->id.'" selected>--'.$additional_tax->name.'--</option>';
                                                                 $tax_format=$additional_tax->type2;
                                                                 $tax_format_id=$additional_tax->id;
                                                                 $tax_format_type=$additional_tax->type3;
                                                                }

                                                            @endphp
                                                            {!! $additional_tax->class == 1 ? "<option value='".numberFormat($additional_tax->value)."' data-type1='$additional_tax->type1' data-type2='$additional_tax->type2' data-type3='$additional_tax->type3' data-type4='$additional_tax->id'>$additional_tax->name</option>" : "" !!}
                                                        @endforeach

                                                        <option value="0" data-type1="%" data-type2="off"
                                                                data-type3="off">{{trans('general.off')}}</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-6">

                                                    <div class="form-group">
                                                        <label for="discountFormat"
                                                               class="caption">{{trans('general.discount')}}</label>
                                                        <select class="form-control round" name="discountFormat" 
                                                                onchange="changeDiscountFormat()"
                                                                id="discountFormat">
                                                            @php
                                                                $discount_format='%';
                                                            @endphp
                                                            @foreach($additionals as $additional_discount)
                                                                @php
                                                                    if(@$defaults[3][0]['feature_value'] == $additional_discount->id && $additional_discount->class == 2){
                                                                     echo '<option value="'.$additional_discount->value.'" data-type1="'.$additional_discount->type1.'" data-type2="'.$additional_discount->type2.'" data-type3="'.$additional_discount->type3.'" selected>--'.$additional_discount->name.'--</option>';
                                                                     $discount_format=$additional_discount->type1;
                                                                    }

                                                                @endphp
                                                                {!! $additional_discount->class == 2 ? "<option value='$additional_discount->value' data-type1='$additional_discount->type1' data-type2='$additional_discount->type2' data-type3='$additional_discount->type3'>$additional_discount->name</option>" : "" !!}
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                               <div class="form-group row">

                                                 <div class="col-sm-4">

                                                    <div class="form-group">
                                                        <label for="discountFormat"
                                                               class="caption"> {{trans('warehouses.warehouse')}}</label>
                                                        <select id="s_warehouses" name="s_warehouses" 
                                                    class="form-control round ">
                                                             <option value="0">{{trans('general.all')}}</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{$warehouse->id}}" {{$warehouse->id==@$defaults[1][0]['feature_value'] ? 'selected' : ''}}>{{$warehouse->title}}</option>
                                                @endforeach

                                                        </select>
                                                    </div>
                                                </div>

                                                 <div class="col-sm-8">

                                                    <div class="form-group">
                                                        <label for="project"
                                                               class="caption">Projects</label>
                                                        <select  name="all_project_id" class="form-control round"
                                                                onchange="changeProject()"
                                                                id="project_id">
                                                             <option value="">Select Project</option>
                                                @foreach($projects as $project)
                                    <option value="{{$project->id}}"
                                        data-type1="{{$project->customer_project->id}}" data-type2="{{$project->branch->id}}" 
                                        data-type3="{{$project->customer_project->company}} {{$project->branch->name}}-{{$project->name}}-{{$project->tid}}"
                                        > {{$project->customer_project->company}} {{$project->branch->name}}-{{$project->name}}-{{$project->tid}}</option>
                                                @endforeach

                                                        </select>
                                                    </div>
                                                </div>






                                            

</div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="toAddInfo"
                                                           class="caption">{{trans('general.note')}}*</label>

                                                    {{ Form::textarea('note', null, ['class' => 'form-control round required', 'placeholder' => trans('general.note'),'rows'=>'2']) }}
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>



                     


        <div class="tab-pane active in" id="active1"
             aria-labelledby="active-tab1" role="tabpanel">



                                <div id="saman-row">



                                    <table class="table-responsive tfr my_stripe">

                                        <thead>
                                        <tr class="item_header bg-gradient-directional-blue white ">
                                            <th width="30%" class="text-center">Reference</th>
                                            <th width="8%" class="text-center">{{trans('general.quantity')}}</th>
                                            <th width="10%" class="text-center">{{trans('general.rate')}}</th>
                                            <th width="10%" class="text-center">{{trans('general.tax_p')}}</th>
                                            <th width="10%" class="text-center">{{trans('general.tax')}}</th>
                                            <th width="7%" class="text-center">{{trans('general.discount')}}</th>
                                            <th width="10%" class="text-center">{{trans('general.amount')}}
                                                ({{config('currency.symbol')}})
                                            </th>
                                            <th width="5%" class="text-center">{{trans('general.action')}}</th>
                                        </tr>

                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><input type="text" class="form-control" name="product_name[]"
                                                       placeholder="{{trans('general.enter_product')}}"
                                                       id='productname-0'>
                                            </td>
                                            <td><input type="text" class="form-control req amnt" name="product_qty[]"
                                                       id="amount-0"
                                                       onkeypress="return isNumber(event)"
                                                       onkeyup="rowTotal('0'), billUpyog()"
                                                       autocomplete="off" value="1"><input type="hidden" id="alert-0"
                                                       value="" name="alert[]"></td>
                                            <td><input type="text" class="form-control req prc" name="product_price[]"
                                                       id="price-0"
                                                       onkeypress="return isNumber(event)"
                                                       onkeyup="rowTotal('0'), billUpyog()"
                                                       autocomplete="off"></td>
                                            <td><input type="text" class="form-control vat " name="product_tax[]"
                                                       id="vat-0"
                                                       onkeypress="return isNumber(event)"
                                                       onkeyup="rowTotal('0'), billUpyog()"
                                                       autocomplete="off"></td>
                                            <td class="text-center" id="texttaxa-0">0</td>
                                            <td><input type="text" class="form-control discount"
                                                       name="product_discount[]"
                                                       onkeypress="return isNumber(event)" id="discount-0"
                                                       onkeyup="rowTotal('0'), billUpyog()" autocomplete="off"></td>
                                            <td><span class="currenty">{{config('currency.symbol')}}</span>
                                                <strong><span class='ttlText' id="result-0">0</span></strong></td>
                                            <td class="text-center">

                                            </td>
                                            <input type="hidden" name="total_tax[]" id="taxa-0" value="0">
                                            <input type="hidden" name="total_discount[]" id="disca-0" value="0">
                                            <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-0"
                                                   value="0">
                                            <input type="hidden" class="pdIn" name="product_id[]" id="pid-0" value="0">
                                            <input type="hidden" name="unit[]" id="unit-0" value="">
                                            <input type="hidden" name="code[]" id="hsn-0" value="">
                                            <input type="hidden" name="taxedvalue[]" id="taxedvalue-0" value="">
                                            <input type="hidden" name="salevalue[]" id="salevalue-0" value="">
                                            
                                        </tr>
                                        <tr>
                                            <td colspan="2"><textarea id="dpid-0" class="form-control html_editor"
                                                                      name="product_description[]"
                                                                      placeholder="{{trans('general.enter_description')}} (Optional)"
                                                                      autocomplete="off"></textarea><br></td>
                                         <td colspan="4"><input type="text" class="form-control" name="project[]"
                                                       placeholder="Search  Project By Project Name , Clent, Branch"
                                                       id='project-0'>

                                                          <input type="hidden" name="inventory_project_id[]" id="project_id-0" >
                                                          <input type="hidden" name="client_id[]" id="client_id-0" >
                                                          <input type="hidden" name="branch_id[]" id="branch_id-0" >
                                            </td>
                                       
                                          
                                          
                                            <td colspan="2"><select class="form-control unit" data-uid="0" name="u_m[]" style="display: none">

                                                </select></td>
                                        </tr>

                                        <tr class="last-item-row sub_c">
                                            <td class="add-row">
                                                <button type="button" class="btn btn-success" aria-label="Left Align"
                                                        id="addinvoice">
                                                    <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                                                </button>
                                            </td>
                                            <td colspan="7"></td>
                                        </tr>

                                        <tr class="sub_c" style="display: table-row;">
                                            <td colspan="6"
                                                align="right">{{ Form::hidden('subtotal','0',['id'=>'subttlform']) }}
                                                <strong>{{trans('general.total_tax')}}</strong>
                                            </td>
                                            <td align="left" colspan="2"><span
                                                        class="currenty lightMode">{{config('currency.symbol')}}</span>
                                                <span id="taxr" class="lightMode">0</span></td>
                                       
                                                <input type="hidden" name="totalsaleamount" id="totalsaleamount" >
                                                <input type="hidden" name="totaltaxabe" id="totaltaxabe" >
                                                <input type="hidden" name="totaldiscount" id="totaldiscount" >
                                                <input type="hidden" name="totaltax" id="totaltax" >
                                                <input type="hidden" id="grandTaxs" name="grandtaxs">
                                                <input type="hidden" id="grandtaxable" name="grandtaxable">
                                                <input type="hidden" id="finalTotals" name="finaltotals">
                                              <input type="hidden" id="grandDiscounts" name="granddiscounts">
                                              <input type="hidden" id="totalLinetotals" name="totalcredit">
                                             
                                            
                                        </tr>
                                        <tr class="sub_c" style="display: table-row;">
                                            <td colspan="6" align="right">
                                                <strong>{{trans('general.total_discount')}}</strong></td>
                                            <td align="left" colspan="2"><span
                                                        class="currenty lightMode"></span>
                                                <span id="discs" class="lightMode">0</span></td>
                                        </tr>

                                    


                                        <tr class="sub_c" style="display: table-row;">
                                      
                                            <td colspan="6" align="right"><strong>{{trans('general.grand_total')}}
                                                    (<span
                                                            class="currenty lightMode">{{config('currency.symbol')}}</span>)</strong>
                                            </td>
                                            <td align="left" colspan="2"><input type="text" name="total"
                                                                                class="form-control"
                                                                                id="invoiceyoghtml" readonly="">

                                            </td>

                                        </tr>
                                            <tr class="sub_c" style="display: table-row;">
                                            <td colspan="2"></td>
                                            <td align="right" colspan="5"><input type="submit"
                                                                                 class="btn btn-success sub-btn btn-lg"
                                                                                 value="{{trans('general.generate')}}"
                                                                                 id="submit-data"
                                                                                 data-loading-text="Creating...">

                                            </td>
                                        </tr>
                                      
                                        </tbody>
                                    </table>
                                    <div class="row mt-3">
                                        <div class="col-12">{!! $fields !!}</div>
                                    </div>


                                </div>


                                    </div>

                                    <!---endtab1-->



                                    


   <!---endtab2-->







                                 





                                <input type="hidden" value="new_i" id="inv_page">
                                <input type="hidden" value="{{route('biller.invoices.store')}}" id="action-url">
                                <input type="hidden" value="search" id="billtype">
                                <input type="hidden" value="0" name="counter" id="ganak">
                                 <input type="hidden" value="0" name="counter" id="expganak">
                                 <input type="hidden" value="0" name="counter" id="itemganak">
                                <input type="hidden" value="{{$tax_format}}" name="tax_format_static" id="tax_format">
                                <input type="hidden" value="{{$tax_format_type}}" name="tax_format"
                                       id="tax_format_type">
                                <input type="hidden" value="{{$tax_format_id}}" name="tax_id" id="tax_format_id">
                                <input type="hidden" value="{{$discount_format}}"
                                       name="discount_format" id="discount_format">

                                @if(@$defaults[4][0]->ship_tax['id']>0) <input type='hidden'
                                    value='{{numberFormat($defaults[4][0]->ship_tax['value'])}}'
                            name='ship_rate' id='ship_rate'><input
                                 type='hidden' value='{{$defaults[4][0]->ship_tax['type2']}}'
                                        name='ship_tax_type'
                                        id='ship_taxtype'>
                                @else
                                    <input type='hidden'
                                           value='{{numberFormat(0)}}'
                                           name='ship_rate' id='ship_rate'><input
                                            type='hidden' value='none' name='ship_tax_type'
                                            id='ship_taxtype'>
                                @endif


                                <input type="hidden" value="0" name="ship_tax" id="ship_tax">
                                <input type="hidden" value="0" id="custom_discount">
                                

                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
    @include("focus.modal.supplier")
   
@endsection
@section('extra-scripts')
 {{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
 <script type="text/javascript">
  $("#project_id").select2();

      $(function () {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{date(config('core.user_date_format'))}}');
            editor();
        });


      $("input[name=payer_type]").on('change', function () {

            var p_t = $('input[name=payer_type]:checked').val();

          if(p_t!='walkin'){
            $('#suppliers-box').attr('readonly',false);
            $('#suppliers-box').val('');
            $('#taxid').val('');
            $('#payer-name').val('');
            $('#taxid').attr('readonly',true);
            $('#payer-name').attr('readonly',true);
            $('#payer_id').val('');

            



        }else{
             
              $('#suppliers-box').attr('readonly',true);
              $('#suppliers-box').val('');
              $('#taxid').val('');
              $('#payer-name').val('');
              $('#taxid').attr('readonly',false);
            $('#payer-name').attr('readonly',false);
            $('#payer_id').val('');
        }
    

      });

    $(".user-box-new").keyup(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var box_id = $(this).attr('data-section');
         var p_t = $('input[name=payer_type]:checked').val();
        $.ajax({
            type: "POST",
            url: baseurl +'transactions/payer_search',
            data: 'keyword=' + $(this).val()+ '&payer_type=' + p_t,
            beforeSend: function () {
                $("#" + box_id + "-box").css("background", "#FFF url(" + baseurl + "assets/custom/load-ring.gif) no-repeat 165px");
            },
            success: function (data) {
                $("#" + box_id + "-box-result").show();
                $("#" + box_id + "-box-result").html(data);
                $("#" + box_id + "-box").css("background", "none");
            }
        });
    });

      function selectPayer(data) {
            $('#payer_id').val(data.id);
            $('#relation_id').val(data.relation_id);
            $('#payer-name').val(data.name);
            //console.log(data);
            $('#taxid').val(data.taxid);

            $("#suppliers-box-result").hide();
        }




</script>
@endsection
