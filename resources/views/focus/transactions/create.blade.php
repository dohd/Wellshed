@extends ('core.layouts.app')

@section ('title', trans('labels.backend.transactions.management') . ' | ' . trans('labels.backend.transactions.create'))

@section('page-header')
    <h1>
        {{ trans('labels.backend.transactions.management') }}
        <small>{{ trans('labels.backend.transactions.create') }}</small>
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
                                                <div class="col-sm-6"><label for="payer"
                                                                             class="caption">Voucher ID*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::number('tid', @$last_id->tid+1, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.tid')]) }}
                                                    </div>
                                                </div>
                                                 <div class="col-sm-6"><label for="transaction_date"
                                            class="caption">Invice Date*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                       aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('transaction_date', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                                    </div>
                                                </div>

                                                 
                                            </div>

                                            
                                             
                                            
                                     
                                        </div>
                                    </div>
                                    <div class="col-sm-6 cmp-pnl">
                                        <div class="inner-cmp-pnl">


                                          
                                          
                                           
                                              
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



                                 <div class="table-responsive" style="margin-top: 10px">
                            <table class="table table-bordered table-hover" id="debtAccVoucher"> 
                                <thead>
                                    <tr>
                                        <th class="text-center">Account Name</th>
                                    
                                          <th class="text-center">Debit</th>
                                          <th class="text-center">Credit</th>
                                          <th class="text-center">Action</th>  
                                    </tr>
                                </thead>
                                <tbody id="debitvoucher">
                                   
                                    <tr>
                                        <td class="" >  
       <select name="account_id[]" id="cmbCode_1" class="form-control tags" >

        @foreach($accounts as $account)
                        <option value="{{$account['id']}}">{{$account['number'].' - '.$account['holder']}}</option>
                    @endforeach


        

       </select>
        
       </select>

                                         </td>
                                    
                                        <td><input type="text" name="debit[]" value="0" class="form-control total_price change"  id="txtAmount_1" onkeyup="calculation(1)" onkeypress="return isNumber(event)" >
                                           </td>
                                            <td ><input type="text" name="credit[]" value="0" class="form-control total_price1 change"  id="txtAmount1_1" onkeyup="calculation(1)" onkeypress="return isNumber(event)" >
                                           </td>
                                       <td>
                                                <button style="text-align: right;" class="btn btn-danger " type="button" value="3" onclick="deleteRow(this)"><i class="fa fa-trash-o"></i></button>
                                            </td>
                                    </tr>                              
                              
                                </tbody>                               
                             <tfoot>
                                    <tr>
                                      <td >
                                            <input type="button" id="add_more" class="btn btn-info" name="add_more"  onClick="addaccount('debitvoucher');" value="Add More" />
                                        </td>
                                       
                                        <td class="text-right">
                                            <input type="text" id="grandTotal" class="form-control text-right " name="total_debit" value="" readonly="readonly" value="0"/>
                                        </td>
                                         <td class="text-right">
                                            <input type="text" id="grandTotal1" class="form-control text-right " name="total_credit" value="" readonly="readonly" value="0"/>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                                      <div class="edit-form-btn">
                                          <input type="submit"
                                                                                 class="btn btn-success sub-btn btn-lg"
                                                                                 value="Post Journal"
                                                                                 id="submit-data"
                                                                                 data-loading-text="Creating...">
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->

                                    </div>

                                
                                <input type="hidden" value="new_i" id="inv_page">
                                <input type="hidden" value="{{route('biller.transactions.store')}}" id="action-url">
                               
                               

                                
                                

                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
  
    
   
@endsection
@section('extra-scripts')
 {{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
   {{ Html::script('focus/js/select2.min.js') }}
  <script type="text/javascript">
        $(document).ready(function () {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{date(config('core.user_date_format'))}}');
        });

        function selectPayer(data) {
            $('#payer_id').val(data.id);
            $('#relation_id').val(data.relation_id);
            $('#payer').val(data.name);
            $("#payer-box-result").hide();
        }

        $("#payer").keyup(function () {
            if ($(this).val() == '') $("#payer-box-result").hide();
            if ($('input[name=payer_type]:checked').val()) {
                var p_t = $('input[name=payer_type]:checked').val();
            } else {
                var p_t = $('input[name=payer_type]').val();
            }
            $.ajax({
                type: "POST",
                url: '{{route('biller.transactions.payer_search')}}',
                data: 'keyword=' + $(this).val() + '&payer_type=' + p_t,
                beforeSend: function () {
                    $("#payer").css("background", "#FFF url(" + baseurl + "assets/custom/load-ring.gif) no-repeat 165px");
                },
                success: function (data) {
                    $("#payer-box-result").show();
                    $("#payer-box-result").html(data);
                    $("#payer-box").css("background", "none");

                }
            });
        });

           $(".tags").select2();
       function addaccount(divName){
    var row = $("#debtAccVoucher tbody tr").length;
    var count = row + 1;
    var limits = 500;
    var tabin = 0;
    if (count == limits) alert("You have reached the limit of adding " + count + " inputs");
    else {
          var newdiv = document.createElement('tr');
          var tabin="cmbCode_"+count;
          var tabindex = count * 2;
          newdiv = document.createElement("tr");
           
          newdiv.innerHTML ="<td> <select name='account_id[]' id='cmbCode_"+ count +"' class='form-control tags' ><?php foreach ($accounts as $acc2) {?><option value='<?php echo $acc2->id;?>'><?php echo $acc2->number;?>- <?php echo $acc2->holder;?></option><?php }?></select></td><td><input type='text' name='debit[]' class='form-control total_price change' value='0' id='txtAmount_"+ count +"' onkeyup='calculation("+ count +")' onkeypress='return isNumber(event)'></td><td><input type='text' name='credit[]' class='form-control total_price1 change' id='txtAmount1_"+ count +"' value='0' onkeyup='calculation("+ count +")' onkeypress='return isNumber(event)'></td><td><button style='text-align: right;' class='btn btn-danger ' type='button' value='delete' onclick='deleteRow(this)'><i class='fa fa-trash-o'></i></button></td>";
          document.getElementById(divName).appendChild(newdiv);
          document.getElementById(tabin).focus();
          count++;
           
          $(".tags").select2({
              placeholder: "Select option",
              allowClear: true
          });
        }
    }
    function calculation(sl) {
        var gr_tot1=0;
        var gr_tot = 0;
        $(".total_price").each(function() {
            isNaN(this.value) || 0 == this.value.length || (gr_tot += parseFloat(this.value));
        });

 $(".total_price1").each(function() {
            isNaN(this.value) || 0 == this.value.length || (gr_tot1 += parseFloat(this.value))
        });

 


        $("#grandTotal").val( accounting.formatNumber(gr_tot));
         $("#grandTotal1").val(accounting.formatNumber(gr_tot1));
    }


     function deleteRow(e) {
        var t = $("#debtAccVoucher > tbody > tr").length;
        if (1 == t) alert("There only one row you can't delete.");
        else {
            var a = e.parentNode.parentNode;
            a.parentNode.removeChild(a)
        }
        calculation()
    }

$(document).on('change keyup blur','.change',function(){ 
                            
            id_arr = $(this).attr('id');
            id = id_arr.split("_");
            var debit=$('#txtAmount_'+id[1]).val();
            var credit=$('#txtAmount1_'+id[1]).val();
            

if(debit > 0){
    $('#txtAmount1_'+id[1]).attr('readonly', true);
     $('#txtAmount1_'+id[1]).val(0);
}else{
$('#txtAmount1_'+id[1]).attr('readonly', false);
     

}



if(credit > 0){
    $('#txtAmount_'+id[1]).attr('readonly', true);
     $('#txtAmount_'+id[1]).val(0);
}else{
$('#txtAmount_'+id[1]).attr('readonly', false);
     

}
            
                
});
   






      $("input[name=payer_type]").on('change', function () {

            var p_t = $('input[name=payer_type]:checked').val();

console.log(p_t);
          if(p_t!='none'){
            $('#payer').attr('readonly',false);
            //$('#payer_id').val('');

            



        }else{
             
            $('#payer').attr('readonly',true);
            $('#payer_id').val('');
        }
    

      }); 



    </script>
@endsection

