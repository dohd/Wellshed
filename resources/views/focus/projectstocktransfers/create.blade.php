@extends ('core.layouts.app')

@section ('title',  ' Stock Project Transfer | Create ')

@section('page-header')
    <h1>
       Stock Project Transfer Management 
        <small>Create Transfer</small>
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
                                                <div class="fcol-sm-12"><h3 class="title">Project
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="frmSearch col-sm-12">
                                                    {{ Form::label( 'cst', 'Search Project',['class' => 'caption']) }}
                                                    {{ Form::text('cst', null, ['class' => 'form-control round project-box', 'placeholder' =>'Enter Project Name,Project Number ,Client Or Branch', 'id'=>'projects-box','data-section'=>'projects','autocomplete'=>'off']) }}
                                                    <div id="projects-box-result"></div>
                                                </div>
                                            </div>
                                            <div id="customer">
                                                <div class="clientinfo">Project Details
                                                    <hr>
                                                    <div id="project_name"></div>
                                                </div>
                                                <hr>
                                                 <div id="tid"></div>
                                                
                                                 <hr>
                                                <div class="clientinfo">
                                                    <div id="client_name"></div>
                                                </div>
                                                 <hr>
                                                <div class="clientinfo">
                                                    <div id="branch_name"></div>
                                                </div>
                                                <hr>
                                               
                                            </div>

                                            {{ Form::hidden('project_id', '0',['id'=>'project_id']) }}
                                            {{ Form::hidden('branch_id', '0',['id'=>'branch_id']) }}
                                         {{ Form::hidden('payer_id', '0',['id'=>'customer_id']) }}
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
                                                <div class="col-sm-6"><label for="invocieno"
                                                                             class="caption">Transaction ID</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                                                             aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::number('tid', @$last_id->tid+1, ['class' => 'form-control round', 'placeholder' => trans('purchaseorders.tid')]) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="invocieno"
                                                                             class="caption">{{trans('general.reference')}}</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                                             aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('refer_no', null, ['class' => 'form-control round', 'placeholder' => trans('general.reference')]) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">

                                                <div class="col-sm-6"><label for="transaction_date"
                                                                             class="caption">Transaction Date</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar4"
                                                                                             aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('transaction_date', null, ['class' => 'form-control round required', 'placeholder' => trans('purchaseorders.invoicedate'),'data-toggle'=>'datepicker','autocomplete'=>'false']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="invocieduedate"
                                                                             class="caption">{{trans('warehouses.warehouse')}}</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-calendar-o"
                                                                                             aria-hidden="true"></span>
                                                        </div>

                                                <select id="s_warehouses" name="s_warehouses" class="form-control round required">
                                                <option value="0">{{trans('general.all')}}</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{$warehouse->id}}" {{$warehouse->id==@$defaults[1][0]['feature_value'] ? 'selected' : ''}}>{{$warehouse->title}}</option>
                                                @endforeach
                                            </select>




                                                        
                                                    </div>
                                                </div>
                                            </div>
                                             <div class="form-group row">
                                                <div class="col-sm-6"><label for="invocieno"
                                                                             class="caption">Requested By</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-file-text-o"
                                                                                             aria-hidden="true"></span>
                                                        </div>

                                                        {{ Form::text('requested_by', null, ['class' => 'form-control round', 'placeholder' => 'Enter Name ']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6"><label for="invocieno"
                                                                             class="caption">Approved By</label>

                                                    <div class="input-group">
                                                        <div class="input-group-addon"><span class="icon-bookmark-o"
                                                                                             aria-hidden="true"></span>
                                                        </div>
                                                        {{ Form::text('approved_by', null, ['class' => 'form-control round', 'placeholder' => 'Enter']) }}
                                                    </div>
                                                </div>
                                            </div>

                                          
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <label for="toAddInfo"
                                                           class="caption">{{trans('general.note')}}</label>

                                                    {{ Form::textarea('notes', null, ['class' => 'form-control round', 'placeholder' => trans('general.note'),'rows'=>'2']) }}
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>


                                <div id="saman-row">
                                    <table class="table-responsive tfr my_stripe">

                                        <thead>
                                        <tr class="item_header bg-gradient-directional-blue white">
                                            <th width="35%" class="text-center">{{trans('general.item_name')}}</th>
                                            <th width="12%" class="text-center">{{trans('general.quantity')}}</th>
                                            <th width="18%" class="text-center">{{trans('general.rate')}}</th>
                                            <th width="15%" class="text-center">UOM</th>
                                          
                                            <th width="15%" class="text-center">{{trans('general.amount')}}
                                                ({{config('currency.symbol')}})
                                            </th>
                                            <th width="5%" class="text-center">{{trans('general.action')}}</th>
                                        </tr>

                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><input type="text" class="form-control" name="product_name[]"
                                                       placeholder="{{trans('general.enter_product')}}"
                                                       id='transfer_productname-0'>
                                            </td>
                                            <td><input type="text" class="form-control req amnt" name="product_qty[]"
                                                       id="amount-0"
                                                       onkeypress="return isNumber(event)"
                                                       onkeyup="transferRowTotal('0'), transferBillUpyog()"
                                                       autocomplete="off" value="1"><input type="hidden" id="alert-0"
                                                                                           value=""
                                                                                           name="alert[]"></td>
                                            <td><input type="text" class="form-control req prc" name="product_price[]"
                                                       id="price-0"
                                                       onkeypress="return isNumber(event)"
                                                       onkeyup="transferRowTotal('0'), transferBillUpyog()"
                                                       autocomplete="off"></td>
                                            <td ><select class="form-control unit" data-uid="0" name="u_m[]"
                                                                    style="display: none">

                                                </select></td>
                                         
                                            <td><span class="currenty">{{config('currency.symbol')}}</span>
                                                <strong><span class='ttlText' id="result-0">0</span></strong></td>
                                            <td class="text-center">

                                            </td>
                                          
                                            <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-0"
                                                   value="0">
                                            <input type="hidden" class="pdIn" name="product_id[]" id="pid-0" value="0">
                                            <input type="hidden" name="unit[]" id="unit-0" value="">
                                            <input type="hidden" name="code[]" id="hsn-0" value="">
                                        </tr>
                                      

                                        <tr class="last-item-row sub_c">
                                            <td class="add-row">
                                                <button type="button" class="btn btn-success" aria-label="Left Align"
                                                        id="transferaddproduct">
                                                    <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                                                </button>
                                            </td>
                                            <td colspan="5"></td>
                                        </tr>

                                      
                                  


                                        <tr class="sub_c" style="display: table-row;">
                                         
                                            <td colspan="4" align="right"><strong>{{trans('general.grand_total')}}
                                                    (<span
                                                            class="currenty lightMode">{{config('currency.symbol')}}</span>)</strong>
                                            </td>
                                    <td align="left" colspan="2"><input type="text" name="total"
                                                                                class="form-control"
                                                                                id="invoiceyoghtml" readonly="">

                                            </td>
                                        </tr>
                                        <tr class="sub_c" style="display: table-row;">
                                          
                                            <td align="right" colspan="8">
                                        <input type="submit" class="btn btn-success sub-btn btn-lg"
                                        value="Confirm & Post" id="submit-data"
                                         data-loading-text="Creating...">

                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div class="row mt-3">
                                        <div class="col-12">{!! $fields !!}</div>
                                    </div>
                                </div>

                                <input type="hidden" value="new_i" id="inv_page">
                                <input type="hidden" value="{{route('biller.projectstocktransfers.store')}}" id="action-url">
                                <input type="hidden" value="search" id="billtype">
                                <input type="hidden" value="0" name="counter" id="ganak">
                               <input type="hidden" value="{{$discount_format}}"
                                       name="discount_format" id="discount_format">

                              

                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    @include("focus.modal.supplier")
@endsection
@section('extra-scripts')
    <script type="text/javascript">
               $(function () {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{date(config('core.user_date_format'))}}');
            editor();
        });


$('#transferaddproduct').on('click', function () {
    var cvalue = parseInt($('#ganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#ganak').val(nxt);
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row div').length;
//product row
    var data = '<tr><td><input type="text" class="form-control" name="product_name[]" placeholder="Enter Product name or Code" id="transfer_productname-' + cvalue + '"></td><td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="transferRowTotal(' + functionNum + '), transferBillUpyog()" autocomplete="off" value="1" ><input type="hidden" id="alert-' + cvalue + '" value=""  name="alert[]"> </td><td><input type="text" class="form-control req prc" name="product_price[]" id="price-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="transferRowTotal(' + functionNum + '), transferBillUpyog()" autocomplete="off"></td><td><select class="form-control unit" data-uid="' + cvalue + '" name="u_m[]" style="display: none"></select></td><td><span class="currenty">' + currency + '</span> <strong><span class=\'ttlText\' id="result-' + cvalue + '">0</span></strong></td><td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn btn-danger removeProdTransfer" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' + cvalue + '" value="0"><input type="hidden" class="pdIn" name="product_id[]" id="pid-' + cvalue + '" value="0"><input type="hidden" name="unit[]" id="unit-' + cvalue + '" attr-org="" value=""><input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value=""><input type="hidden" name="unit_m[]" id="unit_m-' + cvalue + '" value="1"><input type="hidden" name="serial[]" id="serial-' + cvalue + '" value=""></tr>';
    //ajax request
    // $('#saman-row').append(data);
    $('tr.last-item-row').before(data);

    row = cvalue;

    $('#transfer_productname-' + cvalue).autocomplete({
        source: function (request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseurl + 'products/search/' + billtype,
                dataType: "json",
                method: 'post',
                data: 'keyword=' + request.term + '&type=product_list&row_num=' + row + '&wid=' + $("#s_warehouses option:selected").val() + '&serial_mode=' + $("#serial_mode:checked").val(),
                success: function (data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.name,
                            value: item.name,
                            data: item
                        };
                    }));
                }
            });
        },
        autoFocus: true,
        minLength: 0,
        select: function (event, ui) {
            id_arr = $(this).attr('id');
            id = id_arr.split("-");
              var dup;
            $('.pdIn').each(function () {
                if ($(this).val() == ui.item.data.id) dup = true;
            });

            if (dup) {
                alert('Already Exists!!');
                return;
            }
        $('#amount-').val(1);
        $('#price-' + id[1]).val(accounting.formatNumber(ui.item.data.purchase_price));
        $('#pid-' + id[1]).val(ui.item.data.id);
        $('#unit-' + id[1]).val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
        $('#hsn-' + id[1]).val(ui.item.data.code);
        $('#alert-' + id[1]).val(ui.item.data.alert);
        $('#serial-' + id[1]).val(ui.item.data.serial);
      
            transferRowTotal(cvalue);
            transferBillUpyog();
            if (typeof unit_load === "function") {
                unit_load();
                $('.unit').show();
            }
        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });

});





               $('#transfer_productname-0').autocomplete({
    source: function (request, response) {
        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        $.ajax({
            url: baseurl + 'products/search/' + billtype,
            dataType: "json",
            method: 'post',
            data: 'keyword=' + request.term + '&type=product_list&row_num=1&wid=' + $("#s_warehouses option:selected").val() + '&serial_mode=' + $("#serial_mode:checked").val(),
            success: function (data) {
                response($.map(data, function (item) {
                    return {
                        label: item.name,
                        value: item.name,
                        data: item
                    };
                }));
            }
        });
    },
    autoFocus: true,
    minLength: 0,
    select: function (event, ui) {
     
        
        $('#amount-0').val(1);
        $('#price-0').val(accounting.formatNumber(ui.item.data.purchase_price));
        $('#pid-0').val(ui.item.data.id);
        $('#unit-0').val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
        $('#hsn-0').val(ui.item.data.code);
        $('#alert-0').val(ui.item.data.alert);
        $('#serial-0').val(ui.item.data.serial);
        $('.unit').show();
        unit_load();
        transferRowTotal(0);
        transferBillUpyog();
    }
});

               var transferRowTotal = function (numb) {
    //most res
    var result;
    var page = '';
    var totalValue = 0;
    var amountVal = accounting.unformat($("#amount-" + numb).val(), accounting.settings.number.decimal);
    var priceVal = accounting.unformat($("#price-" + numb).val(), accounting.settings.number.decimal);
  
    var totalPrice = amountVal.toFixed(two_fixed) * priceVal;
 
    if ($("#inv_page").val() == 'new_i' && formInputGet("#pid", numb) > 0) {
        var alertVal = accounting.unformat($("#alert-" + numb).val(), accounting.settings.number.decimal);
        if (alertVal <= +amountVal) {
            var aqt = alertVal - amountVal;
            alert('Low Stock! ' + accounting.formatNumber(aqt));
        }
    }

    $("#result-" + numb).html(accounting.formatNumber(totalPrice));
  
    $("#total-" + numb).val(accounting.formatNumber(totalPrice));
    transferSamanYog();
};




var transferBillUpyog = function () {


    var totalBillVal = accounting.formatNumber(transferSamanYog());
    $("#mahayog").html(totalBillVal);
    $("#subttlform").val(accounting.formatNumber(samanYog()));
    $("#invoiceyoghtml").val(totalBillVal);
    $("#bigtotal").html(totalBillVal);
};


//product total
var transferSamanYog = function () {
    var itempriceList = [];
    var idList = [];
    var r = 0;
    $('.ttInput').each(function () {
        var vv = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var vid = $(this).attr('id');
        vid = vid.split("-");
        itempriceList.push(vv);
        idList.push(vid[1]);
        r++;
    });
    var sum = 0;
    var taxc = 0;
    var discs = 0;
    for (var z = 0; z < idList.length; z++) {
        var x = idList[z];
        if (itempriceList[z] > 0) {
            sum += itempriceList[z];
        }
        
    }

    return accounting.unformat(sum, accounting.settings.number.decimal);
};



$('#saman-row').on('click', '.removeProdTransfer', function () {

    var pidd = $(this).closest('tr').find('.pdIn').val();
    var retain = $(this).closest('tr').attr('data-re');

    var pqty = $(this).closest('tr').find('.amnt').val();
    pqty = pidd + '-' + pqty;
    if (retain) {
        $('<input>').attr({
            type: 'hidden',
            id: 'restock',
            name: 'restock[]',
            value: pqty
        }).appendTo('form');
    }
    $(this).closest('tr').remove();
    $('#d' + $(this).closest('tr').find('.pdIn').attr('id')).closest('tr').remove();
    $('.amnt').each(function (index) {
        transferRowTotal(index);
        transferBillUpyog();
    });


    return false;
});

    </script>
@endsection
