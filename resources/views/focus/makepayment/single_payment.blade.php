@extends ('core.layouts.app')

@section ('title',  'Purchases  | Create')

@section('page-header')
    <h1>
       Purchases
        <small>Create</small>
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
 <h3 class="title">Single Payment Portal 
                                                    </h3>
                                                </div>
                                            </div>
                                         
                                           




                                            

                                               <div class="form-group row">
                                                <div class="col-sm-8"><label for="payer"
                                                                             class="caption">Supplire Name*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::text('payer', @$transactions->payer, ['class' => 'form-control round required', 'placeholder' => 'Supplier Name','id'=>'payer-name','readonly']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-4"><label for="taxid"
                                                                             class="caption">Total Amount</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('amount_to_pay', @$transactions->total_amount, ['class' => 'form-control round', 'placeholder' => 'Tax Id','id'=>'taxid','readonly']) }}
                                                    </div>
                                                </div>

                                                  {{ Form::hidden('payer_id', @$transactions->payer_id,['id'=>'payer_id']) }}
                                                  {{ Form::hidden('id', @$transactions->id,['id'=>'id']) }}
                                            </div>

                                               <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="toAddInfo"
                                                           class="caption">Transaction Description</label>

                                                    {{ Form::textarea('transactiondescription', @$transactions->note, ['class' => 'form-control round ', 'placeholder' => trans('general.note'),'rows'=>'2','readonly']) }}
                                                      <input type="hidden" value="new_i" id="payment_page">
                                <input type="hidden" value="{{route('biller.makepayments.store')}}" id="action-url">
                                
                                                </div>
                                            </div>

                                            
                                                <div class="form-group row">

                                             

                                                 <div class="col-sm-8">

                                                    <div class="form-group">
                                                        <label for="account_id"
                                                               class="caption">Ledger Account(Credited)*</label>
                                                        <select  name="account_id" class="form-control round required" id="account_id">
                                                            <option value="">Select Ledger Account</option>
                                                            @foreach($accounts as $account)

                                                                @if($account->holder !== 'Stock Gain' && $account->holder !== 'Others' && $account->holder !== 'Point of Sale' && $account->holder !== 'Loan Penalty Receivable' && $account->holder !== 'Loan Interest Receivable')
                                                                    <option value="{{$account->id}}"> {{$account->holder}}</option>
                                                                @endif

                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>

                                                    <div class="col-sm-4"><label for="amount_paid"
                                                                             class="caption">Total Amount</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('amount_paid', @$transactions->total_amount, ['class' => 'form-control round', 'placeholder' => 'Amount Paid','id'=>'amount_paid']) }}
                                                    </div>
                                                </div>

         <div class="col-sm-12">

                                                    <div class="form-group">
                                                       
                                                        <input type="submit"
                                  class="btn btn-success sub-btn btn-lg"
                                    value="Post Payment" id="submit-data"
                                     data-loading-text="Creating...">
                                                    </div>
                                                </div>                                       

</div>

                                        </div>
                                    </div>


                                    <div class="col-sm-6 cmp-pnl">
                                        <div class="inner-cmp-pnl">


                                            <div class="form-group row">

                                                <div class="col-sm-12"><h3
                                                            class="title">payment Details</h3>
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
                                                  <div class="col-sm-6"><label for="transaction_date"
                                            class="caption">Payment Date*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                       aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('transaction_date', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                                    </div>
                                                </div>



                                             
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="ref_type"
                                                                             class="caption">Payment Method*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                         <select id="method" name="method" 
                                                    class="form-control round required  ">
                                                   @foreach(payment_methods() as $payment_method)
                                    <option value="{{$payment_method}}">{{$payment_method}}</option>
                                @endforeach
                                <option value="Card">Card</option>
                                <option value="Wallet">Wallet {{trans('payments.wallet_balance')}}</option>
                                           

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="refer_no"
                                                                             class="caption">Payment {{trans('general.reference')}} *</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('refer_no', null, ['class' => 'form-control round required', 'placeholder' => trans('general.reference')]) }}
                                                    </div>
                                                </div>
                                            </div>
                                           

                                            
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="toAddInfo"
                                                           class="caption">{{trans('general.note')}}*</label>

                                                    {{ Form::textarea('note', null, ['class' => 'form-control round required', 'placeholder' => trans('general.note'),'rows'=>'2']) }}
                                                      <input type="hidden" value="new_i" id="payment_page">
                                <input type="hidden" value="{{route('biller.makepayments.store')}}" id="action-url">
                                
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>



                          















                              
                                

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
