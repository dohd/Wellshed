     <div class="row">
 <div class="col-sm-6 cmp-pnl">
                                        <div id="customerpanel" class="inner-cmp-pnl">

                                              <div class="form-group row">
                                                <div class="fcol-sm-12">
 <h3 class="title">Customer Info 
                                                    </h3>
                                                </div>
                                            </div>

      <div class="form-group row">

                                                  <div class='col-md-12'>
        <div class='col m-1'>
           
                {{ Form::label( 'method', trans('transactions.payer_type'),['class' => 'col-12 control-label']) }}
                <div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-primary" name="client_status" id="colorCheck1"
                           value="customer" checked="">
                    <label class="custom-control-label" for="colorCheck1">Existing</label>
                </div>

                <div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-purple" name="client_status" value="new" 
                           id="colorCheck3">
                    <label class="custom-control-label" for="colorCheck3">New Client</label>
                </div>

              
                
              
           
        </div>

    </div>

                                             </div>



   <div class="form-group row">

                                                <div class="col-sm-6"><label for="client_id"
                                                                             class="caption">Customer*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                        <select id="person" name="client_id" class="form-control required select-box"  data-placeholder="{{trans('customers.customer')}}" >
                                </select>
                                                    </div>
                                                </div>
                                                 <div class="col-sm-6"><label for="ref_type"
                                                                             class="caption">Branch</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                          <select id="branch_id" name="branch_id" class="form-control  select-box"  data-placeholder="Branch" >
                                </select>
                                                    </div>
                                                </div>
                                            </div>


                                           

                                            <div class="form-group row">

                                                <div class="col-sm-6"><label for="client_name"
                                                                             class="caption"> Name</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('client_name', null, ['class' => 'form-control round required', 'placeholder' => trans('general.reference'),'id'=>'payer-name', 'readonly']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="client_email"
                                                                             class="caption"> Email</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('client_email', null, ['class' => 'form-control round required', 'placeholder' => trans('general.reference'),'id'=>'client_email', 'readonly']) }}
                                                    </div>
                                                </div>

                                               
                                            </div>


                                             <div class="form-group row">

                                                
                                              
                                                 <div class="col-sm-6"><label for="client_contact"
                                                                             class="caption"> Contact</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('client_contact', null, ['class' => 'form-control round required', 'placeholder' => 'Contact','id'=>'client_contact', 'readonly']) }}
                                                    </div>
                                                </div>
                                            </div>

    


                                        </div>
                                    </div>






                                      <div class="col-sm-6 cmp-pnl">
                                        <div class="inner-cmp-pnl">

                                             <div class="form-group row">

                                                <div class="col-sm-12"><h3
                                                            class="title">Lead Info</h3>
                                                </div>

                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-6"><label for="reference"
                                                                             class="caption">Lead ID*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::number('reference', @$last_lead->reference+1, ['class' => 'form-control round', 'placeholder' => trans('purchaseorders.tid')]) }}
                                                    </div>
                                                </div>

                                                  <div class="col-sm-6"><label for="date_of_request"
                                            class="caption">Date Of Request*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                       aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('date_of_request', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                                    </div>
                                                </div>
                                             
                                            </div>


                                            <div class="form-group row">
                                               

                                                 <div class="col-sm-12"><label for="title"
                                                                             class="caption"> Subject or Title*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('title', null, ['class' => 'form-control round required', 'placeholder' => 'Title']) }}
                                                    </div>
                                                </div>
                                             
                                            </div>


                                            

                                            <div class="form-group row">

                                                <div class="col-sm-6"><label for="source"
                                                                             class="caption">Source*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                         <select id="ref_type" name="source" 
                                                    class="form-control round required  ">
                                                    <option value="">Select Source*</option>
                                                  <option value="Emergency Call">Emergency Call</option>
                                                  <option value="RFQ" >RFQ</option>
                                                     <option value="Site Survey" >Site Survey</option>
                                                     <option value="Tender" >Tender</option>
                                           

                                                        </select>
                                                    </div>
                                                </div>
                                                 <div class="col-sm-6"><label for="ref_type"
                                                                             class="caption">Assign To*</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                          aria-hidden="true"></span>
                                                        </div>

                                                       <select class="form-control  select-box" name="employee_id" id="employee"
                                        data-placeholder="{{trans('tasks.assign')}}" >

                                    @foreach($employees as $employee)
                                        <option value="{{$employee['id']}}">{{$employee['first_name']}} {{$employee['last_name']}}</option>
                                    @endforeach
                                </select>
                                                    </div>
                                                </div>
                                            </div>

                                             <div class="form-group row">
                                                
                                                <div class="col-sm-12"><label for="refer_no"
                                                                             class="caption">Note</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                           aria-hidden="true"></span>
                                                        </div>
                                                         {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'),'autocomplete'=>'off']) }}</div>
                                                    </div>
                                                </div>









                                            </div>




                                            
                                        </div>
                                    </div>



    </div>











@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $(document).ready(function () {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{date(config('core.user_date_format'))}}');
        });

        
    </script>


     {{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
 <script type="text/javascript">
 $("#employee").select2();

      



      $("input[name=client_status]").on('change', function () {

            var p_t = $('input[name=client_status]:checked').val();

        

          if(p_t!='customer'){
            $('#person').attr('disabled',true);
             $('#branch_id').attr('disabled',true);
            
            $('#person').val('');
            $('#branch_id').val('');
            $('#payer-name').attr('readonly',false);
            $('#client_email').attr('readonly',false);
            $('#client_contact').attr('readonly',false);


            $('#payer-name').val('');
            $('#client_email').val('');
            $('#client_contact').val('');
  

            



        }else{
             
            $('#person').attr('disabled',false);
             $('#branch_id').attr('disabled',false);
            $('#person').val('');
            $('#branch_id').val('');

            $('#payer-name').attr('readonly',true);
            $('#client_email').attr('readonly',true);
            $('#client_contact').attr('readonly',true);
            $('#client_email').val('');
            $('#client_contact').val('');

             
        }
    

      });

    $(".user-box-new").keyup(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var box_id = $(this).attr('data-section');
         var p_t = $('input[name=client_status]:checked').val();
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


             $("#person").select2({
                tags: [],
                ajax: {
                    url: '{{route('biller.customers.select')}}',
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: function (person) {
                        return {
                            person: person
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name+' - '+item.company,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });

             $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
           $("#person").on('change', function () {
            $("#branch_id").val('').trigger('change');
            var tips = $('#person :selected').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#branch_id").select2({
                ajax: {
                    url: '{{route('biller.branches.branch_load')}}?id=' + tips,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    params: {'cat_id': tips},
                    data: function (product) {
                        return {
                            product: product
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });
        });


</script>
@endsection


