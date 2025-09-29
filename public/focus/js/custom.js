var billtype = $('#billtype').val();
$('#addproduct').on('click', function () {
    var cvalue = parseInt($('#ganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#ganak').val(nxt);
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row div').length;

    //project details
    var project_id = $('#project_id option:selected').val();
    if (project_id = "") {

        var customer_id = "";
        var branch_id = "";
        var project_description = "";

    } else {

        var customer_id = $('#project_id option:selected').attr('data-type1');
        var branch_id = $('#project_id option:selected').attr('data-type2');
        var project_description = $('#project_id option:selected').attr('data-type3');
    }


    
    //product row
    var data = `<tr>
        <td><input type="text" class="form-control" name="product_name[]" placeholder="Enter Product name or Code" id="productname-' + cvalue + '"></td>
        <td>
            <input type="text" class="form-control req amnt" name="product_qty[]" id="amount-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off" value="1" >
            <input type="hidden" id="alert-' + cvalue + '" name="alert[]"> 
        </td>
        <td><input type="text" class="form-control req prc" name="product_price[]" id="price-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"></td>
        <td> <input type="text" class="form-control vat" name="product_tax[]" id="vat-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"></td> <td id="texttaxa-' + cvalue + '" class="text-center">0</td> 
        <td><input type="text" class="form-control discount" name="product_discount[]" onkeypress="return isNumber(event)" id="discount-' + cvalue + '" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"></td> 
        <td><span class="currenty">' + currency + '</span> <strong><span class=\'ttlText\' id="result-' + cvalue + '">0</span></strong></td> 
        <td class="text-center">
            <button type="button" data-rowid="' + cvalue + '" class="btn btn-danger removeProd" title="Remove" > <i class="fa fa-minus-square"></i> </button> 
        </td><input type="hidden" name="total_tax[]" id="taxa-' + cvalue + '" value="0">
        <input type="hidden" name="total_discount[]" id="disca-' + cvalue + '" value="0"><input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' + cvalue + '" value="0"> 
        <input type="hidden" class="pdIn" name="product_id[]" id="pid-' + cvalue + '" value="0"> <input type="hidden" name="unit[]" id="unit-' + cvalue + '" attr-org="" value=""> 
        <input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value=""><input type="hidden" name="unit_m[]" id="unit_m-' + cvalue + '" value="1"> 
        <input type="hidden" name="serial[]" id="serial-' + cvalue + '" value="">
        </tr><tr><td colspan="2"><textarea class="form-control html_editor"  id="dpid-' + cvalue + '" name="product_description[]" placeholder="Enter Product description" autocomplete="off"></textarea><br></td><td colspan="4"><input type="text" class="form-control" name="project[]" placeholder="Search  Project By Project Name , Clent, Branch" id="project-' + cvalue + '"><input type="hidden" name="inventory_project_id[]" id="project_id-' + cvalue + '" ><input type="hidden" name="client_id[]" id="client_id-' + cvalue + '" ><input type="hidden" name="taxedvalue[]" id="taxedvalue-' + cvalue + '" ><input type="hidden" name="salevalue[]" id="salevalue-' + cvalue + '" ><input type="hidden" name="branch_id[]" id="branch_id-' + cvalue + '" ></td><td colspan="2"><select class="form-control unit 1" data-uid="' + cvalue + '" name="u_m[]" style="display: none"></select></td></tr>`;

    //ajax request
    // $('#saman-row').append(data);
    $('tr.last-item-row').before(data);
    editor();
    row = cvalue;

    $('#productname-' + cvalue).autocomplete({
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
            var t_r = ui.item.data.taxrate;
            var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
            if (custom > 0) {
                t_r = custom;
            }
            var discount = ui.item.data.disrate;
            var dup;
            var custom_discount = $('#custom_discount').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);
            $('.pdIn').each(function () {
                if ($(this).val() == ui.item.data.id) dup = true;
            });
            if (dup) {
                alert('Already Exists!!');
                return;
            }
            $('#amount-' + id[1]).val(1);
            $('#price-' + id[1]).val(accounting.formatNumber(ui.item.data.price));
            $('#pid-' + id[1]).val(ui.item.data.id);
            $('#vat-' + id[1]).val(accounting.formatNumber(t_r));
            $('#discount-' + id[1]).val(accounting.formatNumber(discount));
            //  $('#dpid-' + id[1]).val(ui.item.data.product_des);
            $('#unit-' + id[1]).val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
            $('#hsn-' + id[1]).val(ui.item.data.code);
            $('#alert-' + id[1]).val(ui.item.data.alert);
            $('#serial-' + id[1]).val(ui.item.data.serial);
            $('#dpid-' + id[1]).summernote('code', ui.item.data.product_des);
            $("#project-" + id[1]).val(project_description);
            $("#project_id-" + id[1]).val(project_id);
            $("#client_id-" + id[1]).val(customer_id);
            $("#branch_id-" + id[1]).val(branch_id);

            rowTotal(cvalue);
            billUpyog();
            if (typeof unit_load === "function") {
                unit_load();
                $('.unit').show();
            }
        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });
    $('#project-' + cvalue).autocomplete({
        source: function (request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseurl + 'projects/search/' + billtype,
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
            $('#project_id-' + id[1]).val(ui.item.data.id);
            $('#client_id-' + id[1]).val(ui.item.data.client_id);
            $('#branch_id-' + id[1]).val(ui.item.data.branch_id);





        }
    });

});



//caculations
var precentCalc = function (total, percentageVal) {
    var pr = (total / 100) * percentageVal;
    return parseFloat(pr);
};
//format
var deciFormat = function (minput) {
    if (!minput) minput = 0;
    return parseFloat(minput).toFixed(2);
};
var formInputGet = function (iname, inumber) {
    var inputId;
    inputId = iname + '-' + inumber;
    var inputValue = $(inputId).val();

    if (inputValue == '') {

        return 0;
    } else {
        return inputValue;
    }
};

//ship calculation
var coupon = function () {
    var cp = 0;
    if ($('#coupon_amount').val()) {
        cp = accounting.unformat($('#coupon_amount').val(), accounting.settings.number.decimal);
    }
    return cp;
};
var shipTot = function () {
    var ship_val = accounting.unformat($('.shipVal').val(), accounting.settings.number.decimal);
    var ship_p = 0;
    if ($("#taxFormat option:selected").attr('data-trate')) {
        var ship_rate = $("#taxFormat option:selected").attr('data-trate');
    } else {
        var ship_rate = accounting.unformat($('#ship_rate').val(), accounting.settings.number.decimal);
    }
    var tax_status = $("#ship_taxtype").val();
    if (tax_status == 'exclusive') {
        ship_p = (ship_val * ship_rate) / 100;
        ship_val = ship_val + ship_p;
    } else if (tax_status == 'inclusive') {
        ship_p = (ship_val * ship_rate) / (100 + ship_rate);
    }
    $('#ship_tax').val(accounting.formatNumber(ship_p));
    $('#ship_final').html(accounting.formatNumber(ship_p));
    return ship_val;
};

//product total
var samanYog = function () {
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
    var taxable = 0;
    var salet = 0;
    for (var z = 0; z < idList.length; z++) {
        var x = idList[z];
        if (itempriceList[z] > 0) {
            sum += itempriceList[z];
        }
        var t1 = accounting.unformat($("#taxa-" + x).val(), accounting.settings.number.decimal);
        var d1 = accounting.unformat($("#disca-" + x).val(), accounting.settings.number.decimal);
        var tx1 = accounting.unformat($("#taxedvalue-" + x).val(), accounting.settings.number.decimal);
        var sv1 = accounting.unformat($("#salevalue-" + x).val(), accounting.settings.number.decimal);
        if (t1 > 0) {
            taxc += t1;
        }
        if (d1 > 0) {
            discs += d1;
        }
        if (tx1 > 0) {
            taxable += tx1;
        }
        if (sv1 > 0) {
            salet += sv1;
        }
    }

    $("#discs").html(accounting.formatNumber(discs));
    $("#taxr").html(accounting.formatNumber(taxc));

    $("#disctotal").html(accounting.formatNumber(discs));
    $("#totaldiscount").val(accounting.formatNumber(discs));
    $("#taxtotal").html(accounting.formatNumber(taxc));
    $("#totaltax").val(accounting.formatNumber(taxc));

    $("#totaltaxabe").val(accounting.formatNumber(taxable));
    $("#linetotal").html(accounting.formatNumber(salet));
    $("#totalsaleamount").val(accounting.formatNumber(salet));

    return accounting.unformat(sum, accounting.settings.number.decimal);
};

//actions
var deleteRow = function (num) {
    var totalSelector = $("#subttlform");
    var prodttl = accounting.unformat($("#total-" + num).val(), accounting.settings.number.decimal);
    var subttl = accounting.unformat(totalSelector.val(), accounting.settings.number.decimal);
    var totalSubVal = subttl - prodttl;
    totalSelector.val(totalSubVal);
    $("#subttlid").html(accounting.formatNumber(totalSubVal));
    var totalBillVal = totalSubVal + shipTot - coupon;
    //final total
    var clean = accounting.formatNumber(totalBillVal);
    $("#mahayog").html(clean);
    $("#invoiceyoghtml").val(clean);
    $("#invoicetotal").html(clean);
};


var billUpyog = function () {
    var out = 0;
    var disc_val = accounting.unformat($('.discVal').val(), accounting.settings.number.decimal);
    if (disc_val) {
        $("#subttlform").val(accounting.formatNumber(samanYog()));
        var disc_rate = $('#discountFormat option:selected').attr('data-type1');

        switch (disc_rate) {
            case '%':
                out = precentCalc(accounting.unformat($('#subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'b_per':
                out = precentCalc(accounting.unformat($('#subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'flat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
            case 'b_flat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
        }
        out = parseFloat(out).toFixed(two_fixed);

        $('#disc_final').html(accounting.formatNumber(out));
        $('#after_disc').val(accounting.formatNumber(out));
    } else {
        $('#disc_final').html(0);
    }
    var totalBillVal = accounting.formatNumber(samanYog() + shipTot() - coupon() - out);
    $("#mahayog").html(totalBillVal);
    $("#subttlform").val(accounting.formatNumber(samanYog()));
    $("#invoiceyoghtml").val(totalBillVal);
    $("#invoicetotal").html(totalBillVal);
    $("#bigtotal").html(totalBillVal);
    
    grandTotal();
};


var rowTotal = function (numb) {
    //most res
    var result;
    var page = '';
    var totalValue = 0;
    var taxableValue = 0;
    var saleValue = 0;
    var amountVal = accounting.unformat($("#amount-" + numb).val(), accounting.settings.number.decimal);
    var priceVal = accounting.unformat($("#price-" + numb).val(), accounting.settings.number.decimal);
    var discountVal = accounting.unformat($("#discount-" + numb).val(), accounting.settings.number.decimal);
    var vatVal = accounting.unformat($("#vat-" + numb).val(), accounting.settings.number.decimal);
    var taxo = 0;
    var disco = 0;
    var totalPrice = amountVal.toFixed(two_fixed) * priceVal;
    var tax_status = $("#taxFormat option:selected").attr('data-type2');
    var disFormat = $("#discount_format").val();
    if ($("#inv_page").val() == 'new_i' && formInputGet("#pid", numb) > 0) {
        var alertVal = accounting.unformat($("#alert-" + numb).val(), accounting.settings.number.decimal);
        if (alertVal <= +amountVal) {
            var aqt = alertVal - amountVal;
            alert('Low Stock! ' + accounting.formatNumber(aqt));
        }
    }
    //tax after bill
    
    if (tax_status == 'exclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax

            var Inpercentage = precentCalc(totalPrice, vatVal);

            totalValue = totalPrice + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalValue - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalValue, discountVal);
                totalValue = totalValue - discount;
                disco = accounting.formatNumber(discount);
            }

            if (vatVal > 0) {
                taxableValue = accounting.formatNumber(totalPrice - discount);
            } else {
                taxableValue = 0;
            }

            taxableValue = taxableValue;
            saleValue = accounting.formatNumber(totalPrice - discount);

        } else {
            //before tax
            if (disFormat == 'b_flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

            //tax
            var Inpercentage = precentCalc(totalValue, vatVal);
            if (vatVal > 0) {
                taxableValue = totalValue;
            } else {
                taxableValue = 0;
            }



            saleValue = totalValue;
            totalValue = totalValue + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
        }

    } else if (tax_status == 'inclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax
            var Inpercentage = (totalPrice * vatVal) / (100 + vatVal);
            var Vatexclusive = (totalPrice * 100) / (100 + vatVal);
            totalValue = totalPrice;
            // taxo = accounting.formatNumber(Inpercentage);

            if (disFormat == 'flat') {

                var discount = discountVal;
                totalValue = Vatexclusive - discountVal; //taxable value
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);





            } else if (disFormat == '%') {
                var discount = precentCalc(Vatexclusive, discountVal);
                totalValue = Vatexclusive - discount; //taxable value

                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);



            }


        } else {
            //before tax





            if (disFormat == 'b_flat') {
                var Vatexclusive = (totalPrice * 100) / (100 + vatVal);
                totalValue = totalPrice;
                var discount = discountVal;
                totalValue = Vatexclusive - discountVal; //taxable value
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }

                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);


            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);

                var Inpercentage = (totalValue * vatVal) / (100 + vatVal);
                totalValue = totalValue;
                taxo = accounting.formatNumber(Inpercentage);
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue - Inpercentage);;
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue - Inpercentage);
            }
            //tax



        }
    } else {
        taxo = 0;
        taxableValue = 0;

        if (disFormat == '%' || disFormat == 'flat') {
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

            saleValue = totalValue;

        } else {
            //before tax
            if (disFormat == 'b_flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }
        }
        saleValue = totalValue;

    }

    $("#result-" + numb).html(accounting.formatNumber(totalValue));
    $("#taxa-" + numb).val(taxo);
    $("#texttaxa-" + numb).text(taxo);
    $("#disca-" + numb).val(disco);
    $("#total-" + numb).val(accounting.formatNumber(totalValue));
    $("#taxedvalue-" + numb).val(accounting.formatNumber(taxableValue));
    $("#salevalue-" + numb).val(accounting.formatNumber(saleValue));


    samanYog();
};
var changeTaxFormat = function () {
    var t_format = $('#taxFormat option:selected');
    if (t_format.attr('data-type2')) {

        $("#tax_format").val(t_format.attr('data-type2'));
        $("#tax_format_type").val(t_format.attr('data-type3'));
        $("#tax_format_id").val(t_format.attr('data-type4'));
        var trate = t_format.val();

    } else {
        $("#tax_format").val('off');
        var trate = 0;
    }
    var discount_format = $("#discount_format").val();
    trate = accounting.unformat(trate, accounting.settings.number.decimal);
    formatRest(t_format.attr('data-type2'), discount_format, trate);
    formatExpRest(t_format.attr('data-type2'), discount_format, trate);
    formatItemRest(t_format.attr('data-type2'), discount_format, trate);
}

var changeDiscountFormat = function () {
    var d_format = $('#discountFormat option:selected');
    if (d_format.attr('data-type1')) {
        $(".disCol").show();

        $("#discount_format").val(d_format.attr('data-type1'));
    } else {
        $("#discount_format").val(d_format.attr('data-type1'));
        $(".disCol").hide();

    }
    var tax_status = $("#tax_format").val();
    formatRest(tax_status, d_format.attr('data-type1'));
    formatExpRest(tax_status, d_format.attr('data-type1'));
}

function formatRest(taxFormat, disFormat, trate = '') {
    var amntArray = [];
    var idArray = [];
    
    $('.amnt').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var id_e = $(this).attr('id');
        id_e = id_e.split("-");
        idArray.push(id_e[1]);
        amntArray.push(v);
    });
    var prcArray = [];
    $('.prc').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        prcArray.push(v);
    });
    var vatArray = [];
    $('.vat').each(function () {
        if (trate > 0) {
            var v = accounting.unformat(trate, accounting.settings.number.decimal);
            $(this).val(accounting.formatNumber(v));
        } else {
            var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        }
        vatArray.push(v);
    });
    var discountArray = [];
    $('.discount').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        discountArray.push(v);
    });

    var taxr = 0;
    var discsr = 0;
    for (var i = 0; i < idArray.length; i++) {
        var x = idArray[i];
        amtVal = amntArray[i];
        prcVal = prcArray[i];
        vatVal = vatArray[i];
        discountVal = discountArray[i];
        var result = amtVal * prcVal;
        if (vatVal == '') {
            vatVal = 0;
        }
        if (discountVal == '') {
            discountVal = 0;
        }
        var taxableValue = 0;
        var totalPrice = 0;
        var saleValue = 0;
        if (taxFormat == 'exclusive') {
            if (disFormat == '%' || disFormat == 'flat') {
                var Inpercentage = precentCalc(result, vatVal);
                var totalPrice = result;
                var result = result + Inpercentage;
                taxr = taxr + Inpercentage;

                $("#texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#taxa-" + x).val(accounting.formatNumber(Inpercentage));

                if (disFormat == '%') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;

                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalPrice - Inpercentage);
                    } else {
                        taxableValue = 0;
                    }




                    saleValue = accounting.formatNumber(totalPrice - Inpercentage);

                } else if (disFormat == 'flat') {
                    result = parseFloat(result) - parseFloat(discountVal);
                    $("#disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;

                    taxableValue = accounting.formatNumber(totalPrice - discountVal);
                    saleValue = accounting.formatNumber(totalPrice - discountVal);
                }





                $("#taxedvalue-" + x).val(taxableValue);
                $("#salevalue-" + x).val(saleValue);
            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'b_flat') {
                    result = result - discountVal;
                    $("#disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }

                var Inpercentage = precentCalc(result, vatVal);
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(result);
                } else {
                    taxableValue = 0;
                }


                saleValue = result;
                result = result + Inpercentage;
                taxr = taxr + Inpercentage;
                $("#texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#taxa-" + x).val(accounting.formatNumber(Inpercentage));
                $("#taxedvalue-" + x).val(accounting.formatNumber(taxableValue));
                $("#salevalue-" + x).val(accounting.formatNumber(saleValue));

            }
        } else if (taxFormat == 'inclusive') {

            if (disFormat == '%' || disFormat == 'flat') {



                var Vatexclusive = (result * 100) / (100 + vatVal);

                totalPrice = result;




                if (disFormat == '%') {

                    var discount = precentCalc(Vatexclusive, discountVal);
                    $("#disca-" + x).val(accounting.formatNumber(discount));
                    totalValue = Vatexclusive - discount; //taxable value
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalValue);
                    } else {
                        taxableValue = 0;
                    }


                    saleValue = accounting.formatNumber(totalValue);
                    $("#taxedvalue-" + x).val(taxableValue);
                    $("#salevalue-" + x).val(saleValue);
                    var taxonew = (totalValue * vatVal) / (100);
                    taxr = accounting.formatNumber(taxonew);

                    result = (totalValue * (100 + vatVal)) / (100); //total Value

                    $("#texttaxa-" + x).html(taxr);
                    $("#taxa-" + x).val(taxr);




                } else if (disFormat == 'flat') {
                    result = result - discountVal;
                    $("#disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;

                    taxableValue = accounting.formatNumber(totalPrice - taxamount - discountVal);
                    saleValue = accounting.formatNumber(totalPrice - taxamount - discountVal);
                    $("#taxedvalue-" + x).val(taxableValue);
                    $("#salevalue-" + x).val(saleValue);
                }




            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;

                    var Inpercentage = (result * vatVal) / (100 + vatVal);

                    //console.log(result);


                    taxr = taxr + Inpercentage;
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(result - Inpercentage);
                    } else {
                        taxableValue = 0;
                    }




                    saleValue = accounting.formatNumber(result - Inpercentage);

                    $("#texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                    $("#taxa-" + x).val(accounting.formatNumber(Inpercentage));
                    $("#taxedvalue-" + x).val(taxableValue);
                    $("#salevalue-" + x).val(saleValue);




                } else if (disFormat == 'b_flat') {

                    var Vatexclusive = (result * 100) / (100 + vatVal);

                    var discount = discountVal;
                    $("#disca-" + x).val(accounting.formatNumber(discount));
                    totalValue = Vatexclusive - discount; //taxable value
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalValue);
                    } else {
                        taxableValue = 0;
                    }



                    saleValue = accounting.formatNumber(totalValue);
                    $("#taxedvalue-" + x).val(taxableValue);
                    $("#salevalue-" + x).val(saleValue);
                    var taxonew = (totalValue * vatVal) / (100);
                    taxr = accounting.formatNumber(taxonew);

                    result = (totalValue * (100 + vatVal)) / (100); //total Value

                    $("#texttaxa-" + x).html(taxr);
                    $("#taxa-" + x).val(taxr);


                }





            }
        } else {





            var saleValue = accounting.unformat($("#amount-" + x).val(), accounting.settings.number.decimal) * accounting.unformat($("#price-" + x).val(), accounting.settings.number.decimal);;

            if (disFormat == '%' || disFormat == 'flat') {

                var result = accounting.unformat($("#amount-" + x).val(), accounting.settings.number.decimal) * accounting.unformat($("#price-" + x).val(), accounting.settings.number.decimal);
                $("#texttaxa-" + x).html('Off');
                $("#taxa-" + x).val(0);
                taxr += 0;

                if (disFormat == '%') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'flat') {
                    var result = result - discountVal;
                    $("#disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }
            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'b_flat') {
                    result = result - discountVal;
                    $("#disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }
                $("#texttaxa-" + x).html('Off');
                $("#taxa-" + x).val(0);

                taxr += 0;
            }
            $("#taxedvalue-" + x).val(0);
            $("#salevalue-" + x).val(accounting.formatNumber(saleValue - discsr));
        }

        $("#total-" + x).val(accounting.formatNumber(result));
        $("#result-" + x).html(accounting.formatNumber(result));



    }
    var sum = accounting.formatNumber(samanYog());
    $("#subttlid").html(sum);
    $("#taxr").html(accounting.formatNumber(taxr));
    $("#taxtotal").html(accounting.formatNumber(taxr));
    $("#discs").html(accounting.formatNumber(discsr));
    $("#disctotal").html(accounting.formatNumber(discsr));
    $("#totalsaleamount").val(sum);

    billUpyog();
}

//remove productrow


$('#saman-row').on('click', '.removeProd', function () {

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
        rowTotal(index);
        billUpyog();
    });


    return false;
});





$(document).on('click', ".quantity-up", function (e) {
    var spinner = $(this);
    var input = spinner.closest('.quantity').find('input[name="product_qty[]"]');
    var oldValue = accounting.unformat(input.val(), accounting.settings.number.decimal);

    var newVal = oldValue + 1;
    spinner.closest('.quantity').find('input[name="product_qty[]"]').val(accounting.formatNumber(newVal));
    spinner.closest('.quantity').find('input[name="product_qty[]"]').trigger("change");
    var id_arr = $(input).attr('id');
    id = id_arr.split("-");
    rowTotal(id[1]);
    billUpyog();
    return false;
});

$(document).on('click', ".quantity-down", function (e) {
    var spinner = $(this);
    var input = spinner.closest('.quantity').find('input[name="product_qty[]"]');
    var oldValue = accounting.unformat(input.val(), accounting.settings.number.decimal);
    var min = 1;
    if (oldValue <= min) {
        var newVal = oldValue;
    } else {
        var newVal = oldValue - 1;
    }
    spinner.closest('.quantity').find('input[name="product_qty[]"]').val(accounting.formatNumber(newVal));
    spinner.closest('.quantity').find('input[name="product_qty[]"]').trigger("change");
    var id_arr = $(input).attr('id');
    id = id_arr.split("-");
    rowTotal(id[1]);
    billUpyog();
    return false;
});


$('#project-0').autocomplete({

    source: function (request, response) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $.ajax({
            url: baseurl + 'projects/search/' + billtype,
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

        $('#project_id-0').val(ui.item.data.id);
        $('#client_id-0').val(ui.item.data.client_id);
        $('#branch_id-0').val(ui.item.data.branch_id);


    }


});



$('#productname-0').autocomplete({
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
        var t_r = ui.item.data.taxrate;
        var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
        if (custom > 0) {
            t_r = custom;
        }

        var discount = ui.item.data.disrate;
        var custom_discount = $('#custom_discount').val();
        //project details

        var project_id = $('#project_id option:selected').val();

        if (project_id = "") {

            var customer_id = "";
            var branch_id = "";
            var project_description = "";

        } else {

            var customer_id = $('#project_id option:selected').attr('data-type1');
            var branch_id = $('#project_id option:selected').attr('data-type2');
            var project_description = $('#project_id option:selected').attr('data-type3');
        }
        var project_id = $('#project_id option:selected').val();

        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#amount-0').val(1);
        $('#price-0').val(accounting.formatNumber(ui.item.data.price));
        $('#pid-0').val(ui.item.data.id);
        $('#vat-0').val(accounting.formatNumber(t_r));
        $('#discount-0').val(accounting.formatNumber(discount));
        //$('#dpid-0').val(ui.item.data.product_des);
        $('#unit-0').val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
        $('#hsn-0').val(ui.item.data.code);
        $('#alert-0').val(ui.item.data.alert);
        $('#serial-0').val(ui.item.data.serial);
        $('#project-0').val(project_description);
        $('#project_id-0').val(project_id);
        $('#client_id-0').val(customer_id);
        $('#branch_id-0').val(branch_id);

        $('.unit').show();
        unit_load();
        rowTotal(0);
        billUpyog();
        $('#dpid-0').summernote('code', ui.item.data.product_des);
    }
});




//expense start
$('#ledgername-0').autocomplete({
    source: function (request, response) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: baseurl + 'accounts/search/' + billtype,
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
        var project_id = $('#project_id option:selected').val();
        if (project_id = "") {
            var customer_id = "";
            var branch_id = "";
            var project_description = "";

        } else {
            var customer_id = $('#project_id option:selected').attr('data-type1');
            var branch_id = $('#project_id option:selected').attr('data-type2');
            var project_description = $('#project_id option:selected').attr('data-type3');
        }

        var project_id = $('#project_id option:selected').val();
        var t_r = 0;
        var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
        if (custom > 0) {
            t_r = custom;
        }
        var discount = 0;
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#exp_amount-0').val(1);
        $('#expprice-0').val(accounting.formatNumber(ui.item.data.price));
        $('#exp_pid-0').val(ui.item.data.id);
        $('#exp_vat-0').val(accounting.formatNumber(t_r));
        $('#exp_discount-0').val(accounting.formatNumber(discount));
        $('#ledger_id-0').val(ui.item.data.id);
        $('#exp_project-0').val(project_description);
        $('#exp_project_id-0').val(project_id);
        $('#exp_client_id-0').val(customer_id);
        $('#exp_branch_id-0').val(branch_id);

        expRowTotal(0);
        expBillUpyog();

    }
});


var expRowTotal = function (numb) {
    //most res
    var result;
    var page = '';
    var totalValue = 0;
    var taxableValue = 0;
    var saleValue = 0;
    var amountVal = accounting.unformat($("#exp_amount-" + numb).val(), accounting.settings.number.decimal);
    var priceVal = accounting.unformat($("#exp_price-" + numb).val(), accounting.settings.number.decimal);
    var discountVal = accounting.unformat($("#exp_discount-" + numb).val(), accounting.settings.number.decimal);
    var vatVal = accounting.unformat($("#exp_vat-" + numb).val(), accounting.settings.number.decimal);
    var taxo = 0;
    var disco = 0;
    var totalPrice = amountVal.toFixed(two_fixed) * priceVal;
    var tax_status = $("#taxFormat option:selected").attr('data-type2');
    var disFormat = $("#discount_format").val();

    //tax after bill

    if (tax_status == 'exclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax

            var Inpercentage = precentCalc(totalPrice, vatVal);
            totalValue = totalPrice + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalValue - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalValue, discountVal);
                totalValue = totalValue - discount;
                disco = accounting.formatNumber(discount);
            }

            if (vatVal > 0) {
                taxableValue = accounting.formatNumber(totalPrice - discount);
            } else {
                taxableValue = 0;
            }

            taxableValue = taxableValue;
            saleValue = accounting.formatNumber(totalPrice - discount);



        } else {
            //before tax
            if (disFormat == 'b_flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

            //tax
            var Inpercentage = precentCalc(totalValue, vatVal);
            if (vatVal > 0) {
                taxableValue = totalValue;
            } else {
                taxableValue = 0;
            }

            saleValue = totalValue;
            totalValue = totalValue + Inpercentage;
            var Inpercentage = precentCalc(totalValue, vatVal);
            totalValue = totalValue + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
        }

    } else if (tax_status == 'inclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax
            var Vatexclusive = (totalPrice * 100) / (100 + vatVal);
            totalValue = totalPrice;
            var Inpercentage = (totalPrice * vatVal) / (100 + vatVal);
            totalValue = totalPrice;

            taxo = accounting.formatNumber(Inpercentage);
            if (disFormat == 'flat') {
                var discount = discountVal;
                totalValue = Vatexclusive - discountVal; //taxable value
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);


            } else if (disFormat == '%') {
                var discount = precentCalc(Vatexclusive, discountVal);
                totalValue = Vatexclusive - discount; //taxable value

                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);
            }
        } else {
            //before tax
            if (disFormat == 'b_flat') {
                var Vatexclusive = (totalPrice * 100) / (100 + vatVal);
                totalValue = totalPrice;
                var discount = discountVal;
                totalValue = Vatexclusive - discountVal; //taxable value
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }

                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);

                var Inpercentage = (totalValue * vatVal) / (100 + vatVal);
                totalValue = totalValue;
                taxo = accounting.formatNumber(Inpercentage);
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue - Inpercentage);;
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue - Inpercentage);
            }
            //tax
            var Inpercentage = (totalPrice * vatVal) / (100 + vatVal);
            totalValue = totalValue;
            taxo = accounting.formatNumber(Inpercentage);
        }
    } else {
        taxo = 0;
        taxableValue = 0;

        if (disFormat == '%' || disFormat == 'flat') {
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

            saleValue = accounting.formatNumber(totalValue);

        } else {
            //before tax
            if (disFormat == 'b_flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }
        }

        saleValue = accounting.formatNumber(totalValue);
    }

    $("#exp_result-" + numb).html(accounting.formatNumber(totalValue));
    $("#exp_taxa-" + numb).val(taxo);
    $("#exp_texttaxa-" + numb).text(taxo);
    $("#exp_disca-" + numb).val(disco);
    $("#exp_total-" + numb).val(accounting.formatNumber(totalValue));
    $("#exp_taxedvalue-" + numb).val(accounting.formatNumber(taxableValue));
    $("#exp_salevalue-" + numb).val(accounting.formatNumber(saleValue));
    expSamanYog();
};



//exp product total



var expSamanYog = function () {
    var itempriceList = [];
    var idList = [];
    var r = 0;
    $('.exp_ttInput').each(function () {
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
    var taxable = 0;
    var salet = 0;
    for (var z = 0; z < idList.length; z++) {
        var x = idList[z];
        if (itempriceList[z] > 0) {
            sum += itempriceList[z];
        }
        var t1 = accounting.unformat($("#exp_taxa-" + x).val(), accounting.settings.number.decimal);
        var d1 = accounting.unformat($("#exp_disca-" + x).val(), accounting.settings.number.decimal);
        var tx1 = accounting.unformat($("#exp_taxedvalue-" + x).val(), accounting.settings.number.decimal);
        var sv1 = accounting.unformat($("#exp_salevalue-" + x).val(), accounting.settings.number.decimal);
        if (t1 > 0) {
            taxc += t1;
        }
        if (d1 > 0) {
            discs += d1;
        }
        if (tx1 > 0) {
            taxable += tx1;
        }
        if (sv1 > 0) {
            salet += sv1;
        }
    }

    $("#exp_discs").html(accounting.formatNumber(discs));
    $("#exp_taxr").html(accounting.formatNumber(taxc));

    $("#exp_disctotal").html(accounting.formatNumber(discs));
    $("#exp_totaldiscount").val(accounting.formatNumber(discs));
    $("#exp_taxtotal").html(accounting.formatNumber(taxc));
    $("#exp_totaltax").val(accounting.formatNumber(taxc));


    $("#exp_totaltaxabe").val(accounting.formatNumber(taxable));
    $("#exp_linetotal").html(accounting.formatNumber(salet));
    $("#exp_totalsaleamount").val(accounting.formatNumber(salet));


    return accounting.unformat(sum, accounting.settings.number.decimal);
};


var expBillUpyog = function () {
    var out = 0;
    var disc_val = accounting.unformat($('.exp_discVal').val(), accounting.settings.number.decimal);
    if (disc_val) {
        $("#exp_subttlform").val(accounting.formatNumber(expSamanYog()));
        var disc_rate = $('#discountFormat option:selected').attr('data-type1');

        switch (disc_rate) {
            case '%':
                out = precentCalc(accounting.unformat($('#exp_subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'b_per':
                out = precentCalc(accounting.unformat($('#exp_subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'flat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
            case 'b_flat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
        }
        out = parseFloat(out).toFixed(two_fixed);

        $('#exp_disc_final').html(accounting.formatNumber(out));
        $('#exp_after_disc').val(accounting.formatNumber(out));
    } else {
        $('#exp_disc_final').html(0);
    }
    var totalBillVal = accounting.formatNumber(expSamanYog() - coupon() - out);
    // $("#exp_mahayog").html(totalBillVal);
    $("#exp_subttlform").val(accounting.formatNumber(expSamanYog()));
    $("#exp_invoiceyoghtml").val(totalBillVal);
    $("#exp_invoicetotal").html(totalBillVal);
    //$("#exp_bigtotal").html(totalBillVal);

    grandTotal();
};

$('#exp_project-0').autocomplete({

    source: function (request, response) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $.ajax({
            url: baseurl + 'projects/search/' + billtype,
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

        $('#exp_project_id-0').val(ui.item.data.id);
        $('#exp_client_id-0').val(ui.item.data.client_id);
        $('#exp_branch_id-0').val(ui.item.data.branch_id);


    }


});




$('#expaddproduct').on('click', function () {
    var cvalue = parseInt($('#expganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#expganak').val(nxt);

    var project_id = $('#project_id option:selected').val();
    if (project_id = "") {
        var customer_id = "";
        var branch_id = "";
        var project_description = "";

    } else {
        var customer_id = $('#project_id option:selected').attr('data-type1');
        var branch_id = $('#project_id option:selected').attr('data-type2');
        var project_description = $('#project_id option:selected').attr('data-type3');
    }

    var project_id = $('#project_id option:selected').val();
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row-exp div').length;
    //product row
    var data = '<tr><td><input type="text" class="form-control" name="ledger_name[]" placeholder="Enter Ledger" id="ledgername-' + cvalue + '"></td><td><input type="text" class="form-control req exp_amnt" name="exp_product_qty[]" id="exp_amount-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="expRowTotal(' + functionNum + '), expBillUpyog()" autocomplete="off" value="1"></td><td><input type="text" class="form-control req exp_prc" name="exp_product_price[]" id="exp_price-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="expRowTotal(' + functionNum + '), expBillUpyog()" autocomplete="off"></td><td><input type="text" class="form-control exp_vat " name="exp_product_tax[]" id="exp_vat-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="expRowTotal(' + functionNum + '), expBillUpyog()" autocomplete="off"></td><td class="text-center" id="exp_texttaxa-' + cvalue + '">0</td> <td><input type="text" class="form-control exp_discount" name="exp_product_discount[]" onkeypress="return isNumber(event)" id="exp_discount-' + cvalue + '"onkeyup="expRowTotal(' + functionNum + '), expBillUpyog()" autocomplete="off"></td><td><span class="exp_currenty">' + currency + '</span> <strong><span class="exp_ttlText" id="exp_result-' + cvalue + '">0</span></strong></td><td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn btn-danger removeExp" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" name="exp_total_tax[]" id="exp_taxa-' + cvalue + '" value="0"><input type="hidden" name="exp_total_discount[]" id="exp_disca-' + cvalue + '" value="0"><input type="hidden" class="exp_ttInput" name="exp_product_subtotal[]" id="exp_total-' + cvalue + '" value="0"><input type="hidden" name="exp_taxedvalue[]" id="exp_taxedvalue-' + cvalue + '" value=""><input type="hidden" name="exp_salevalue[]" id="exp_salevalue-' + cvalue + '" value=""><input type="hidden" class="exp_pdIn" name="ledger_id[]" id="exp_pid-' + cvalue + '" value="0"></tr><tr><td colspan="3"><textarea id="dexp_pid-' + cvalue + '" class="form-control html_editor" name="exp_product_description[]"placeholder="Enter Expense  description" autocomplete="off"></textarea><br></td><td colspan="5"><input type="text" class="form-control" name="exp_project[]" placeholder="Search  Project By Project Name , Clent, Branch" id="exp_project-' + cvalue + '""><input type="hidden" name="exp_project_id[]" id="exp_project_id-' + cvalue + '" > <input type="hidden" name="exp_client_id[]" id="exp_client_id-' + cvalue + '" > <input type="hidden" name="exp_branch_id[]" id="exp_branch_id-' + cvalue + '" ></td></tr>';
    //ajax request
    // $('#saman-row').append(data);
    $('tr.last-item-row-exp').before(data);
    editor();
    row = cvalue;

    $('#ledgername-' + cvalue).autocomplete({
        source: function (request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseurl + 'accounts/search/' + billtype,
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
            var t_r = ui.item.data.taxrate;
            var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
            if (custom > 0) {
                t_r = custom;
            }
            var discount = ui.item.data.disrate;
            var dup;
            var custom_discount = $('#custom_discount').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);

            $('#exp_amount-' + id[1]).val(1);
            $('#expprice-' + id[1]).val(accounting.formatNumber(ui.item.data.price));
            $('#exp_pid-' + id[1]).val(ui.item.data.id);
            $('#exp_vat-' + id[1]).val(accounting.formatNumber(t_r));
            $('#exp_discount-' + id[1]).val(accounting.formatNumber(discount));
            $('#ledger_id-' + id[1]).val(ui.item.data.id);
            $("#exp_project-" + id[1]).val(project_description);
            $("#exp_project_id-" + id[1]).val(project_id);
            $("#exp_client_id-" + id[1]).val(customer_id);
            $("#exp_branch_id-" + id[1]).val(branch_id);

            expRowTotal(cvalue);
            expBillUpyog();

        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });

    $('#exp_project-' + cvalue).autocomplete({
        source: function (request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseurl + 'projects/search/' + billtype,
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

            $('#exp_project_id-' + id[1]).val(ui.item.data.id);
            $('#exp_client_id-' + id[1]).val(ui.item.data.client_id);
            $('#exp_branch_id-' + id[1]).val(ui.item.data.branch_id);



        }
    });

});

function formatExpRest(taxFormat, disFormat, trate = '') {
    var amntArray = [];
    var idArray = [];

    $('.exp_amnt').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var id_e = $(this).attr('id');
        id_e = id_e.split("-");
        idArray.push(id_e[1]);
        amntArray.push(v);
    });
    var prcArray = [];
    $('.exp_prc').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        prcArray.push(v);
    });
    var vatArray = [];
    $('.exp_vat').each(function () {
        if (trate > 0) {
            var v = accounting.unformat(trate, accounting.settings.number.decimal);
            $(this).val(accounting.formatNumber(v));
        } else {
            var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        }
        vatArray.push(v);
    });
    var discountArray = [];
    $('.exp_discount').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        discountArray.push(v);
    });

    var taxr = 0;
    var discsr = 0;
    for (var i = 0; i < idArray.length; i++) {
        var x = idArray[i];
        amtVal = amntArray[i];
        prcVal = prcArray[i];
        vatVal = vatArray[i];
        discountVal = discountArray[i];
        var result = amtVal * prcVal;
        if (vatVal == '') {
            vatVal = 0;
        }
        if (discountVal == '') {
            discountVal = 0;
        }
        var taxableValue = 0;
        var totalPrice = 0;
        var saleValue = 0;

        if (taxFormat == 'exclusive') {
            if (disFormat == '%' || disFormat == 'flat') {
                var Inpercentage = precentCalc(result, vatVal);
                var totalPrice = result;
                var result = result + Inpercentage;
                taxr = taxr + Inpercentage;

                $("#exp_texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#exp_taxa-" + x).val(accounting.formatNumber(Inpercentage));

                if (disFormat == '%') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#exp_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;

                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalPrice - Inpercentage);
                    } else {
                        taxableValue = 0;
                    }

                    saleValue = accounting.formatNumber(totalPrice - Inpercentage);

                } else if (disFormat == 'flat') {
                    result = parseFloat(result) - parseFloat(discountVal);
                    $("#exp_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                    taxableValue = accounting.formatNumber(totalPrice - discountVal);
                    saleValue = accounting.formatNumber(totalPrice - discountVal);
                }


                $("#exp_taxedvalue-" + x).val(taxableValue);
                $("#exp_salevalue-" + x).val(saleValue);
            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#exp_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'b_flat') {
                    result = result - discountVal;
                    $("#exp_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }

                var Inpercentage = precentCalc(result, vatVal);
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(result);
                } else {
                    taxableValue = 0;
                }


                saleValue = result;
                result = result + Inpercentage;
                taxr = taxr + Inpercentage;
                $("#exp_texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#exp_taxa-" + x).val(accounting.formatNumber(Inpercentage));
                $("#exp_taxedvalue-" + x).val(accounting.formatNumber(taxableValue));
                $("#exp_salevalue-" + x).val(accounting.formatNumber(saleValue));

            }
        } else if (taxFormat == 'inclusive') {

            if (disFormat == '%' || disFormat == 'flat') {

                var Vatexclusive = (result * 100) / (100 + vatVal);

                totalPrice = result;

                if (disFormat == '%') {

                    var discount = precentCalc(Vatexclusive, discountVal);
                    $("#exp_disca-" + x).val(accounting.formatNumber(discount));
                    totalValue = Vatexclusive - discount; //taxable value
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalValue);
                    } else {
                        taxableValue = 0;
                    }


                    saleValue = accounting.formatNumber(totalValue);
                    $("#exp_taxedvalue-" + x).val(taxableValue);
                    $("#exp_salevalue-" + x).val(saleValue);
                    var taxonew = (totalValue * vatVal) / (100);
                    taxr = accounting.formatNumber(taxonew);

                    result = (totalValue * (100 + vatVal)) / (100); //total Value

                    $("#exp_texttaxa-" + x).html(taxr);
                    $("#exp_taxa-" + x).val(taxr);




                } else if (disFormat == 'flat') {
                    result = result - discountVal;
                    $("#exp_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;

                    taxableValue = accounting.formatNumber(totalPrice - taxamount - discountVal);
                    saleValue = accounting.formatNumber(totalPrice - taxamount - discountVal);
                    $("#exp_taxedvalue-" + x).val(taxableValue);
                    $("#exp_salevalue-" + x).val(saleValue);
                }




            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#exp_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;

                    var Inpercentage = (result * vatVal) / (100 + vatVal);

                    //console.log(result);


                    taxr = taxr + Inpercentage;
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(result - Inpercentage);
                    } else {
                        taxableValue = 0;
                    }




                    saleValue = accounting.formatNumber(result - Inpercentage);

                    $("#exp_texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                    $("#exp_taxa-" + x).val(accounting.formatNumber(Inpercentage));
                    $("#exp_taxedvalue-" + x).val(taxableValue);
                    $("#exp_salevalue-" + x).val(saleValue);




                } else if (disFormat == 'b_flat') {

                    var Vatexclusive = (result * 100) / (100 + vatVal);

                    var discount = discountVal;
                    $("#exp_disca-" + x).val(accounting.formatNumber(discount));
                    totalValue = Vatexclusive - discount; //taxable value
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalValue);
                    } else {
                        taxableValue = 0;
                    }



                    saleValue = accounting.formatNumber(totalValue);
                    $("#exp_taxedvalue-" + x).val(taxableValue);
                    $("#exp_salevalue-" + x).val(saleValue);
                    var taxonew = (totalValue * vatVal) / (100);
                    taxr = accounting.formatNumber(taxonew);

                    result = (totalValue * (100 + vatVal)) / (100); //total Value

                    $("#exp_texttaxa-" + x).html(taxr);
                    $("#exp_taxa-" + x).val(taxr);
                }
            }
        } else {

            var saleValue = accounting.unformat($("#exp_amount-" + x).val(), accounting.settings.number.decimal) * accounting.unformat($("#exp_price-" + x).val(), accounting.settings.number.decimal);;

            if (disFormat == '%' || disFormat == 'flat') {

                var result = accounting.unformat($("#exp_amount-" + x).val(), accounting.settings.number.decimal) * accounting.unformat($("#exp_price-" + x).val(), accounting.settings.number.decimal);
                $("#exp_texttaxa-" + x).html('Off');
                $("#exp_taxa-" + x).val(0);
                taxr += 0;

                if (disFormat == '%') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#exp_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'flat') {
                    var result = result - discountVal;
                    $("#exp_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }
            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#exp_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'b_flat') {
                    result = result - discountVal;
                    $("#exp_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }
                $("#exp_texttaxa-" + x).html('Off');
                $("#exp_taxa-" + x).val(0);

                taxr += 0;
            }

            $("#exp_taxedvalue-" + x).val(0);
            $("#exp_salevalue-" + x).val(saleValue - discsr);
        }

        $("#exp_total-" + x).val(accounting.formatNumber(result));
        $("#exp_result-" + x).html(accounting.formatNumber(result));



    }
    var sum = accounting.formatNumber(samanYog());
    $("#exp_subttlid").html(sum);
    $("#exp_taxr").html(accounting.formatNumber(taxr));
    $("#exp_taxtotal").html(accounting.formatNumber(taxr));
    $("#exp_discs").html(accounting.formatNumber(discsr));
    $("#exp_disctotal").html(accounting.formatNumber(discsr));
    $("#exp_totalsaleamount").val(sum);

    expBillUpyog();
}

//remove productrow


$('#saman-row-exp').on('click', '.removeExp', function () {

    var pidd = $(this).closest('tr').find('.exp_pdIn').val();
    var retain = $(this).closest('tr').attr('data-re');

    var pqty = $(this).closest('tr').find('.exp_amnt').val();
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
    $('#d' + $(this).closest('tr').find('.exp_pdIn').attr('id')).closest('tr').remove();
    $('.exp_amnt').each(function (index) {
        expRowTotal(index);
        expBillUpyog();
    });


    return false;
});
//asset and equipment


$('#itemname-0').autocomplete({
    source: function (request, response) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: baseurl + 'assetequipments/search/' + billtype,
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
        var t_r = 0;
        var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
        if (custom > 0) {
            t_r = custom;
        }
        var discount = 0;
        var custom_discount = $('#custom_discount').val();
        if (custom_discount > 0) discount = deciFormat(custom_discount);
        $('#item_amount-0').val(1);
        $('#item_price-0').val(accounting.formatNumber(ui.item.data.cost));
        $('#item_pid-0').val(ui.item.data.id);
        $('#item_vat-0').val(accounting.formatNumber(t_r));
        $('#item_discount-0').val(accounting.formatNumber(discount));
        $('#account_id-0').val(ui.item.data.account_id);
        $('#account_type-0').val(ui.item.data.account_type);
        $('#item_dpid-0').summernote('code', ui.item.data.decription);

        itemRowTotal(0);
        itemBillUpyog();

    }
});
var itemRowTotal = function (numb) {
    //most res
    var result;
    var page = '';
    var totalValue = 0;
    var taxableValue = 0;
    var saleValue = 0;
    var amountVal = accounting.unformat($("#item_amount-" + numb).val(), accounting.settings.number.decimal);
    var priceVal = accounting.unformat($("#item_price-" + numb).val(), accounting.settings.number.decimal);
    var discountVal = accounting.unformat($("#item_discount-" + numb).val(), accounting.settings.number.decimal);
    var vatVal = accounting.unformat($("#item_vat-" + numb).val(), accounting.settings.number.decimal);
    var taxo = 0;
    var disco = 0;
    var totalPrice = amountVal.toFixed(two_fixed) * priceVal;
    var tax_status = $("#taxFormat option:selected").attr('data-type2');
    var disFormat = $("#discount_format").val();

    //tax after bill

    if (tax_status == 'exclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax

            var Inpercentage = precentCalc(totalPrice, vatVal);
            totalValue = totalPrice + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalValue - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalValue, discountVal);
                totalValue = totalValue - discount;
                disco = accounting.formatNumber(discount);
            }

            if (vatVal > 0) {
                taxableValue = accounting.formatNumber(totalPrice - discount);
            } else {
                taxableValue = 0;
            }

            taxableValue = taxableValue;
            saleValue = accounting.formatNumber(totalPrice - discount);



        } else {
            //before tax
            if (disFormat == 'b_flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

            //tax
            var Inpercentage = precentCalc(totalValue, vatVal);
            if (vatVal > 0) {
                taxableValue = totalValue;
            } else {
                taxableValue = 0;
            }

            saleValue = totalValue;
            totalValue = totalValue + Inpercentage;
            var Inpercentage = precentCalc(totalValue, vatVal);
            totalValue = totalValue + Inpercentage;
            taxo = accounting.formatNumber(Inpercentage);
        }

    } else if (tax_status == 'inclusive') {
        if (disFormat == '%' || disFormat == 'flat') {
            //tax
            var Vatexclusive = (totalPrice * 100) / (100 + vatVal);
            totalValue = totalPrice;
            var Inpercentage = (totalPrice * vatVal) / (100 + vatVal);
            totalValue = totalPrice;

            taxo = accounting.formatNumber(Inpercentage);
            if (disFormat == 'flat') {
                var discount = discountVal;
                totalValue = Vatexclusive - discountVal; //taxable value
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);


            } else if (disFormat == '%') {
                var discount = precentCalc(Vatexclusive, discountVal);
                totalValue = Vatexclusive - discount; //taxable value

                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);
            }
        } else {
            //before tax
            if (disFormat == 'b_flat') {
                var Vatexclusive = (totalPrice * 100) / (100 + vatVal);
                totalValue = totalPrice;
                var discount = discountVal;
                totalValue = Vatexclusive - discountVal; //taxable value
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue);
                } else {
                    taxableValue = 0;
                }

                saleValue = accounting.formatNumber(totalValue);
                var taxonew = (totalValue * vatVal) / (100);
                taxo = accounting.formatNumber(taxonew);

                var totalValue = (totalValue * (100 + vatVal)) / (100); //total Value

                disco = accounting.formatNumber(discount);
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);

                var Inpercentage = (totalValue * vatVal) / (100 + vatVal);
                totalValue = totalValue;
                taxo = accounting.formatNumber(Inpercentage);
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(totalValue - Inpercentage);;
                } else {
                    taxableValue = 0;
                }


                saleValue = accounting.formatNumber(totalValue - Inpercentage);
            }
            //tax
            var Inpercentage = (totalPrice * vatVal) / (100 + vatVal);
            totalValue = totalValue;
            taxo = accounting.formatNumber(Inpercentage);
        }
    } else {
        taxo = 0;
        taxableValue = 0;

        if (disFormat == '%' || disFormat == 'flat') {
            if (disFormat == 'flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == '%') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }

            saleValue = accounting.formatNumber(totalValue);

        } else {
            //before tax
            if (disFormat == 'b_flat') {
                disco = accounting.formatNumber(discountVal);
                totalValue = totalPrice - discountVal;
            } else if (disFormat == 'b_per') {
                var discount = precentCalc(totalPrice, discountVal);
                totalValue = totalPrice - discount;
                disco = accounting.formatNumber(discount);
            }
        }

        saleValue = accounting.formatNumber(totalValue);
    }

    $("#item_result-" + numb).html(accounting.formatNumber(totalValue));
    $("#item_taxa-" + numb).val(taxo);
    $("#item_texttaxa-" + numb).text(taxo);
    $("#item_disca-" + numb).val(disco);
    $("#item_total-" + numb).val(accounting.formatNumber(totalValue));
    $("#item_taxedvalue-" + numb).val(accounting.formatNumber(taxableValue));
    $("#item_salevalue-" + numb).val(accounting.formatNumber(saleValue));
    itemSamanYog();
};

var itemSamanYog = function () {
    var itempriceList = [];
    var idList = [];
    var r = 0;
    $('.item_ttInput').each(function () {
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
    var taxable = 0;
    var salet = 0;
    for (var z = 0; z < idList.length; z++) {
        var x = idList[z];
        if (itempriceList[z] > 0) {
            sum += itempriceList[z];
        }
        var t1 = accounting.unformat($("#item_taxa-" + x).val(), accounting.settings.number.decimal);
        var d1 = accounting.unformat($("#item_disca-" + x).val(), accounting.settings.number.decimal);
        var tx1 = accounting.unformat($("#item_taxedvalue-" + x).val(), accounting.settings.number.decimal);
        var sv1 = accounting.unformat($("#item_salevalue-" + x).val(), accounting.settings.number.decimal);
        if (t1 > 0) {
            taxc += t1;
        }
        if (d1 > 0) {
            discs += d1;
        }
        if (tx1 > 0) {
            taxable += tx1;
        }
        if (sv1 > 0) {
            salet += sv1;
        }
    }

    $("#item_discs").html(accounting.formatNumber(discs));
    $("#item_taxr").html(accounting.formatNumber(taxc));

    $("#item_disctotal").html(accounting.formatNumber(discs));
    $("#item_totaldiscount").val(accounting.formatNumber(discs));
    $("#item_taxtotal").html(accounting.formatNumber(taxc));
    $("#item_totaltax").val(accounting.formatNumber(taxc));


    $("#item_totaltaxabe").val(accounting.formatNumber(taxable));
    $("#item_linetotal").html(accounting.formatNumber(salet));
    $("#item_totalsaleamount").val(accounting.formatNumber(salet));
    return accounting.unformat(sum, accounting.settings.number.decimal);
};


var itemBillUpyog = function () {
    var out = 0;
    var disc_val = accounting.unformat($('.item_discVal').val(), accounting.settings.number.decimal);
    if (disc_val) {
        $("#item_subttlform").val(accounting.formatNumber(itemSamanYog()));
        var disc_rate = $('#discountFormat option:selected').attr('data-type1');

        switch (disc_rate) {
            case '%':
                out = precentCalc(accounting.unformat($('#item_subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'b_per':
                out = precentCalc(accounting.unformat($('#item_subttlform').val(), accounting.settings.number.decimal), disc_val);
                break;
            case 'flat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
            case 'b_flat':
                out = accounting.unformat(disc_val, accounting.settings.number.decimal);
                break;
        }
        out = parseFloat(out).toFixed(two_fixed);

        $('#item_disc_final').html(accounting.formatNumber(out));
        $('#item_after_disc').val(accounting.formatNumber(out));
    } else {
        $('#item_disc_final').html(0);
    }
    var totalBillVal = accounting.formatNumber(itemSamanYog() - coupon() - out);
    // $("#exp_mahayog").html(totalBillVal);
    $("#item_subttlform").val(accounting.formatNumber(itemSamanYog()));
    $("#item_invoiceyoghtml").val(totalBillVal);
    $("#item_invoicetotal").html(totalBillVal);
    //$("#exp_bigtotal").html(totalBillVal);

    grandTotal();
};

$('#item_project-0').autocomplete({

    source: function (request, response) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $.ajax({
            url: baseurl + 'projects/search/' + billtype,
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

        $('#item_project_id-0').val(ui.item.data.id);
        $('#item_client_id-0').val(ui.item.data.client_id);
        $('#item_branch_id-0').val(ui.item.data.branch_id);


    }


});








$('#itemaddproduct').on('click', function () {
    var cvalue = parseInt($('#itemganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#itemganak').val(nxt);
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row-item div').length;
    //product row
    var data = '<tr><td><input type="text" class="form-control" name="item_name[]" placeholder="Enter Assets Or Equipments Name" id="itemname-' + cvalue + '"></td><td><input type="text" class="form-control req item_amnt" name="item_product_qty[]" id="item_amount-' + cvalue + '"onkeypress="return isNumber(event)" onkeyup="itemRowTotal(' + functionNum + '), itemBillUpyog()"autocomplete="off" value="1"></td><td><input type="text" class="form-control req item_prc" name="item_product_price[]" id="item_price-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="itemRowTotal(' + functionNum + '), itemBillUpyog()" autocomplete="off"></td><td><input type="text" class="form-control item_vat " name="item_product_tax[]" id="item_vat-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="itemRowTotal(' + functionNum + '), itemBillUpyog()" autocomplete="off"></td><td class="text-center" id="item_texttaxa-' + cvalue + '">0</td><td><input type="text" class="form-control item_discount" name="item_product_discount[]"onkeypress="return isNumber(event)" id="item_discount-' + cvalue + '" onkeyup="itemRowTotal(' + functionNum + '), itemBillUpyog()" autocomplete="off"></td><td><span class="item_currenty">' + currency + '</span><strong><span class="item_ttlText" id="item_result-' + cvalue + '">0</span></strong></td><td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn btn-danger removeItem" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" name="item_total_tax[]" id="item_taxa-' + cvalue + '" value="0"><input type="hidden" name="item_total_discount[]" id="item_disca-' + cvalue + '" value="0"><input type="hidden" class="item_ttInput" name="item_product_subtotal[]" id="item_total-' + cvalue + '" value="0"><input type="hidden" class="item_pdIn" name="item_id[]" id="item_pid-' + cvalue + '" value="0"><input type="hidden"  name="account_id[]" id="account_id-' + cvalue + '" value="0"><input type="hidden"  name="account_type[]" id="account_type-' + cvalue + '" > <input type="hidden" name="item_taxedvalue[]" id="item_taxedvalue-' + cvalue + '" value=""><input type="hidden" name="item_salevalue[]" id="item_salevalue-' + cvalue + '" value=""></tr><tr><td colspan="3"><textarea id="ditem_pid-' + cvalue + '" class="form-control html_editor" name="item_product_description[]" placeholder="Enter Expense  description"autocomplete="off"></textarea><br></td><td colspan="5"><input type="text" class="form-control" name="item_project[]" placeholder="Search  Project By Project Name , Clent, Branch" id="item_project-' + cvalue + '"><input type="hidden" name="item_project_id[]" id="item_project_id-' + cvalue + '" ><input type="hidden" name="item_client_id[]" id="item_client_id-' + cvalue + '" ><input type="hidden" name="item_branch_id[]" id="item_branch_id-' + cvalue + '" ></td></tr>';
    //ajax request
    // $('#saman-row').append(data);
    $('tr.last-item-row-item').before(data);
    editor();
    row = cvalue;

    $('#itemname-' + cvalue).autocomplete({
        source: function (request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseurl + 'assetequipments/search/' + billtype,
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
            var t_r = ui.item.data.taxrate;
            var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
            if (custom > 0) {
                t_r = custom;
            }
            var discount = ui.item.data.disrate;
            var dup;
            var custom_discount = $('#custom_discount').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);

            $('#item_amount-' + id[1]).val(1);
            $('#item_price-' + id[1]).val(accounting.formatNumber(ui.item.data.cost));
            $('#item_pid-' + id[1]).val(ui.item.data.id);
            $('#item_vat-' + id[1]).val(accounting.formatNumber(t_r));
            $('#item_discount-' + id[1]).val(accounting.formatNumber(discount));
            $('#account_id-' + id[1]).val(ui.item.data.account_id);
            $('#account_type-' + id[1]).val(ui.item.data.account_type);
            $('#item_dpid-' + id[1]).summernote('code', ui.item.data.decription);





            itemRowTotal(cvalue);
            itemBillUpyog();


        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });

    $('#item_project-' + cvalue).autocomplete({
        source: function (request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseurl + 'projects/search/' + billtype,
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

            $('#item_project_id-' + id[1]).val(ui.item.data.id);
            $('#item_client_id-' + id[1]).val(ui.item.data.client_id);
            $('#item_branch_id-' + id[1]).val(ui.item.data.branch_id);

        }
    });

});

function formatItemRest(taxFormat, disFormat, trate = '') {
    var amntArray = [];
    var idArray = [];

    $('.item_amnt').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var id_e = $(this).attr('id');
        id_e = id_e.split("-");
        idArray.push(id_e[1]);
        amntArray.push(v);
    });
    var prcArray = [];
    $('.item_prc').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        prcArray.push(v);
    });
    var vatArray = [];
    $('.item_vat').each(function () {
        if (trate > 0) {
            var v = accounting.unformat(trate, accounting.settings.number.decimal);
            $(this).val(accounting.formatNumber(v));
        } else {
            var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        }
        vatArray.push(v);
    });
    var discountArray = [];
    $('.item_discount').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        discountArray.push(v);
    });

    var taxr = 0;
    var discsr = 0;
    for (var i = 0; i < idArray.length; i++) {
        var x = idArray[i];
        amtVal = amntArray[i];
        prcVal = prcArray[i];
        vatVal = vatArray[i];
        discountVal = discountArray[i];
        var result = amtVal * prcVal;
        if (vatVal == '') {
            vatVal = 0;
        }
        if (discountVal == '') {
            discountVal = 0;
        }
        var taxableValue = 0;
        var totalPrice = 0;
        var saleValue = 0;

        if (taxFormat == 'exclusive') {
            if (disFormat == '%' || disFormat == 'flat') {
                var Inpercentage = precentCalc(result, vatVal);
                var totalPrice = result;
                var result = result + Inpercentage;
                taxr = taxr + Inpercentage;

                $("#item_texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#item_taxa-" + x).val(accounting.formatNumber(Inpercentage));

                if (disFormat == '%') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#item_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;

                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalPrice - Inpercentage);
                    } else {
                        taxableValue = 0;
                    }

                    saleValue = accounting.formatNumber(totalPrice - Inpercentage);

                } else if (disFormat == 'flat') {
                    result = parseFloat(result) - parseFloat(discountVal);
                    $("#item_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                    taxableValue = accounting.formatNumber(totalPrice - discountVal);
                    saleValue = accounting.formatNumber(totalPrice - discountVal);
                }


                $("#item_taxedvalue-" + x).val(taxableValue);
                $("#item_salevalue-" + x).val(saleValue);
            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#item_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'b_flat') {
                    result = result - discountVal;
                    $("#item_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }

                var Inpercentage = precentCalc(result, vatVal);
                if (vatVal > 0) {
                    taxableValue = accounting.formatNumber(result);
                } else {
                    taxableValue = 0;
                }


                saleValue = result;
                result = result + Inpercentage;
                taxr = taxr + Inpercentage;
                $("#item_texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                $("#item_taxa-" + x).val(accounting.formatNumber(Inpercentage));
                $("#item_taxedvalue-" + x).val(accounting.formatNumber(taxableValue));
                $("#item_salevalue-" + x).val(accounting.formatNumber(saleValue));

            }
        } else if (taxFormat == 'inclusive') {

            if (disFormat == '%' || disFormat == 'flat') {

                var Vatexclusive = (result * 100) / (100 + vatVal);

                totalPrice = result;

                if (disFormat == '%') {

                    var discount = precentCalc(Vatexclusive, discountVal);
                    $("#item_disca-" + x).val(accounting.formatNumber(discount));
                    totalValue = Vatexclusive - discount; //taxable value
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalValue);
                    } else {
                        taxableValue = 0;
                    }


                    saleValue = accounting.formatNumber(totalValue);
                    $("#item_taxedvalue-" + x).val(taxableValue);
                    $("#item_salevalue-" + x).val(saleValue);
                    var taxonew = (totalValue * vatVal) / (100);
                    taxr = accounting.formatNumber(taxonew);

                    result = (totalValue * (100 + vatVal)) / (100); //total Value

                    $("#item_texttaxa-" + x).html(taxr);
                    $("#item_taxa-" + x).val(taxr);




                } else if (disFormat == 'flat') {
                    result = result - discountVal;
                    $("#item_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;

                    taxableValue = accounting.formatNumber(totalPrice - taxamount - discountVal);
                    saleValue = accounting.formatNumber(totalPrice - taxamount - discountVal);
                    $("#item_taxedvalue-" + x).val(taxableValue);
                    $("#item_salevalue-" + x).val(saleValue);
                }




            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#item_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;

                    var Inpercentage = (result * vatVal) / (100 + vatVal);

                    //console.log(result);


                    taxr = taxr + Inpercentage;
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(result - Inpercentage);
                    } else {
                        taxableValue = 0;
                    }




                    saleValue = accounting.formatNumber(result - Inpercentage);

                    $("#item_texttaxa-" + x).html(accounting.formatNumber(Inpercentage));
                    $("#item_taxa-" + x).val(accounting.formatNumber(Inpercentage));
                    $("#item_taxedvalue-" + x).val(taxableValue);
                    $("#item_salevalue-" + x).val(saleValue);




                } else if (disFormat == 'b_flat') {

                    var Vatexclusive = (result * 100) / (100 + vatVal);

                    var discount = discountVal;
                    $("#item_disca-" + x).val(accounting.formatNumber(discount));
                    totalValue = Vatexclusive - discount; //taxable value
                    if (vatVal > 0) {
                        taxableValue = accounting.formatNumber(totalValue);
                    } else {
                        taxableValue = 0;
                    }



                    saleValue = accounting.formatNumber(totalValue);
                    $("#item_taxedvalue-" + x).val(taxableValue);
                    $("#item_salevalue-" + x).val(saleValue);
                    var taxonew = (totalValue * vatVal) / (100);
                    taxr = accounting.formatNumber(taxonew);

                    result = (totalValue * (100 + vatVal)) / (100); //total Value

                    $("#item_texttaxa-" + x).html(taxr);
                    $("#item_taxa-" + x).val(taxr);
                }
            }
        } else {

            var saleValue = accounting.unformat($("#item_amount-" + x).val(), accounting.settings.number.decimal) * accounting.unformat($("#item_price-" + x).val(), accounting.settings.number.decimal);;

            if (disFormat == '%' || disFormat == 'flat') {

                var result = accounting.unformat($("#item_amount-" + x).val(), accounting.settings.number.decimal) * accounting.unformat($("#item_price-" + x).val(), accounting.settings.number.decimal);
                $("#item_texttaxa-" + x).html('Off');
                $("#item_taxa-" + x).val(0);
                taxr += 0;

                if (disFormat == '%') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#item_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'flat') {
                    var result = result - discountVal;
                    $("#item_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }
            } else {
                if (disFormat == 'b_per') {
                    var Inpercentage = precentCalc(result, discountVal);
                    result = result - Inpercentage;
                    $("#item_disca-" + x).val(accounting.formatNumber(Inpercentage));
                    discsr = discsr + Inpercentage;
                } else if (disFormat == 'b_flat') {
                    result = result - discountVal;
                    $("#item_disca-" + x).val(accounting.formatNumber(discountVal));
                    discsr += discountVal;
                }
                $("#item_texttaxa-" + x).html('Off');
                $("#item_taxa-" + x).val(0);

                taxr += 0;
            }

            $("#item_taxedvalue-" + x).val(0);
            $("#item_salevalue-" + x).val(saleValue - discsr);
        }

        $("#item_total-" + x).val(accounting.formatNumber(result));
        $("#item_result-" + x).html(accounting.formatNumber(result));



    }
    var sum = accounting.formatNumber(samanYog());
    $("#item_subttlid").html(sum);
    $("#item_taxr").html(accounting.formatNumber(taxr));
    $("#item_taxtotal").html(accounting.formatNumber(taxr));
    $("#item_discs").html(accounting.formatNumber(discsr));
    $("#item_disctotal").html(accounting.formatNumber(discsr));
    $("#item_totalsaleamount").val(sum);

    itemBillUpyog();
}

//remove productrow


$('#saman-row-item').on('click', '.removeItem', function () {

    var pidd = $(this).closest('tr').find('.item_pdIn').val();
    var retain = $(this).closest('tr').attr('data-re');

    var pqty = $(this).closest('tr').find('.item_amnt').val();
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
    $('#d' + $(this).closest('tr').find('.item_pdIn').attr('id')).closest('tr').remove();
    $('.item_amnt').each(function (index) {
        expRowTotal(index);
        expBillUpyog();
    });


    return false;
});



var changeProject = function () {
    var project_id = $('#project_id option:selected');



    if (project_id == "") {

        var customer_id = "";
        var branch_id = "";
        var project_description = "";
        var project_id = 0;

    } else {

        var customer_id = project_id.attr('data-type1');
        var branch_id = project_id.attr('data-type2');
        var project_description = project_id.attr('data-type3');
        var project_id = project_id.val();


    }


    projectChange(project_id, customer_id, branch_id, project_description);
    expProjectChange(project_id, customer_id, branch_id, project_description);
    itemProjectChange(project_id, customer_id, branch_id, project_description);
}

function projectChange(project_id, customer_id, branch_id, project_description) {


    var idArray = [];

    $('.amnt').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var id_e = $(this).attr('id');
        id_e = id_e.split("-");
        idArray.push(id_e[1]);

    });

    for (var i = 0; i < idArray.length; i++) {
        var x = idArray[i];
        $("#project-" + x).val(project_description);
        $("#project_id-" + x).val(project_id);
        $("#client_id-" + x).val(customer_id);
        $("#branch_id-" + x).val(branch_id);
    }


}

function expProjectChange(project_id, customer_id, branch_id, project_description) {

    var idArray = [];

    $('.exp_amnt').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var id_e = $(this).attr('id');
        id_e = id_e.split("-");
        idArray.push(id_e[1]);

    });

    for (var i = 0; i < idArray.length; i++) {
        var x = idArray[i];
        $("#exp_project-" + x).val(project_description);
        $("#exp_project_id-" + x).val(project_id);
        $("#exp_client_id-" + x).val(customer_id);
        $("#exp_branch_id-" + x).val(branch_id);
    }


}

function itemProjectChange(project_id, customer_id, branch_id, project_description) {

    var idArray = [];

    $('.item_amnt').each(function () {
        var v = accounting.unformat($(this).val(), accounting.settings.number.decimal);
        var id_e = $(this).attr('id');
        id_e = id_e.split("-");
        idArray.push(id_e[1]);

    });

    for (var i = 0; i < idArray.length; i++) {
        var x = idArray[i];
        $("#item_project-" + x).val(project_description);
        $("#item_project_id-" + x).val(project_id);
        $("#item_client_id-" + x).val(customer_id);
        $("#item_branch_id-" + x).val(branch_id);
    }


}

var grandTotal = function () {
    var linetotal = accounting.unformat($("#totalsaleamount").val(), accounting.settings.number.decimal);
    var exp_linetotal = accounting.unformat($("#exp_totalsaleamount").val(), accounting.settings.number.decimal);
    var item_linetotal = accounting.unformat($("#item_totalsaleamount").val(), accounting.settings.number.decimal);

    var disctotal = accounting.unformat($("#totaldiscount").val(), accounting.settings.number.decimal);
    var exp_disctotal = accounting.unformat($("#exp_totaldiscount").val(), accounting.settings.number.decimal);
    var item_disctotal = accounting.unformat($("#item_totaldiscount").val(), accounting.settings.number.decimal);

    var taxtotal = accounting.unformat($("#totaltax").val(), accounting.settings.number.decimal);
    var exp_taxtotal = accounting.unformat($("#exp_totaltax").val(), accounting.settings.number.decimal);
    var item_taxtotal = accounting.unformat($("#item_totaltax").val(), accounting.settings.number.decimal);
    var item_totaltaxabe = accounting.unformat($("#item_totaltax").val(), accounting.settings.number.decimal);

    var invoicetotal = accounting.unformat($("#invoiceyoghtml").val(), accounting.settings.number.decimal);
    var exp_invoicetotal = accounting.unformat($("#exp_invoiceyoghtml").val(), accounting.settings.number.decimal);
    var item_invoicetotal = accounting.unformat($("#item_invoiceyoghtml").val(), accounting.settings.number.decimal);

    var totaltaxabe = accounting.unformat($("#totaltaxabe").val(), accounting.settings.number.decimal);
    var exp_totaltaxabe = accounting.unformat($("#exp_totaltaxabe").val(), accounting.settings.number.decimal);
    var item_totaltaxabe = accounting.unformat($("#item_totaltaxabe").val(), accounting.settings.number.decimal);

    var totalLinetotal = accounting.formatNumber(linetotal + exp_linetotal + item_linetotal);
    var totalDiscount = accounting.formatNumber(disctotal + exp_disctotal + item_disctotal);
    var totalTax = accounting.formatNumber(taxtotal + exp_taxtotal + item_taxtotal);
    var finalTotal = accounting.formatNumber(invoicetotal + exp_invoicetotal + item_invoicetotal);
    var grandtaxable = accounting.formatNumber(totaltaxabe + exp_totaltaxabe + item_totaltaxabe);

    $("#totalLinetotals").val(totalLinetotal);
    $("#totalLinetotal").html(totalLinetotal);

    $("#grandDiscounts").val(totalDiscount);
    $("#grandDiscount").html(totalDiscount);

    $("#grandTaxs").val(totalTax);
    $("#grandTax").html(totalTax);

    $("#finalTotals").val(finalTotal);
    $("#finalTotal").html(finalTotal);

    $("#grandtaxable").val(grandtaxable);

    // pos order panel totals
    const taxRate = $('#taxFormat').val() / 100;
    const subtotal = accounting.unformat($("#bigtotal").text());
    const taxAmount = subtotal * taxRate;
    const total = subtotal * (1 + taxRate);
    $('#taxr').text(accounting.formatNumber(taxAmount));
    $("#bigtotal").text(accounting.formatNumber(total));

    // payment modal totals
    $("#mahayog").text(accounting.formatNumber(total));

    $("#invoiceyoghtml").val(accounting.formatNumber(total));
    $("#tax_total").val(accounting.formatNumber(taxAmount));
    $("#tax_format_id").val($('#taxFormat').val());
}


//quotation
$('#addinvoice').on('click', function () {
    var cvalue = parseInt($('#ganak').val()) + 1;
    var nxt = parseInt(cvalue);
    $('#ganak').val(nxt);
    var functionNum = "'" + cvalue + "'";
    count = $('#saman-row div').length;

    //project details

    var project_id = $('#project_id option:selected').val();


    //console.log(77);
    if (project_id = "") {

        var customer_id = "";
        var branch_id = "";
        var project_description = "";

    } else {

        var customer_id = $('#project_id option:selected').attr('data-type1');
        var branch_id = $('#project_id option:selected').attr('data-type2');
        var project_description = $('#project_id option:selected').attr('data-type3');
    }

    //product row
    var data = `<tr><td><input type="text" class="form-control" name="product_name[]" placeholder="Enter Reference" id="productname-' + cvalue + '"></td><td><input type="text" class="form-control req amnt" name="product_qty[]" id="amount-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off" value="1" ><input type="hidden" id="alert-' + cvalue + '" value=""  name="alert[]"> </td> <td><input type="text" class="form-control req prc" name="product_price[]" id="price-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"></td><td> <input type="text" class="form-control vat" name="product_tax[]" id="vat-' + cvalue + '" onkeypress="return isNumber(event)" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"></td> <td id="texttaxa-' + cvalue + '" class="text-center">0</td> <td><input type="text" class="form-control discount" name="product_discount[]" onkeypress="return isNumber(event)" id="discount-' + cvalue + '" onkeyup="rowTotal(' + functionNum + '), billUpyog()" autocomplete="off"></td> <td><span class="currenty">' + currency + '</span> <strong><span class=\'ttlText\' id="result-' + cvalue + '">0</span></strong></td> <td class="text-center"><button type="button" data-rowid="' + cvalue + '" class="btn btn-danger removeProd" title="Remove" > <i class="fa fa-minus-square"></i> </button> </td><input type="hidden" name="total_tax[]" id="taxa-' + cvalue + '" value="0"><input type="hidden" name="total_discount[]" id="disca-' + cvalue + '" value="0"><input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' + cvalue + '" value="0"> <input type="hidden" class="pdIn" name="product_id[]" id="pid-' + cvalue + '" value="0"> <input type="hidden" name="unit[]" id="unit-' + cvalue + '" attr-org="" value=""> <input type="hidden" name="hsn[]" id="hsn-' + cvalue + '" value=""><input type="hidden" name="unit_m[]" id="unit_m-' + cvalue + '" value="1"> <input type="hidden" name="serial[]" id="serial-' + cvalue + '" value=""></tr><tr><td colspan="2"><textarea class="form-control html_editor"  id="dpid-' + cvalue + '" name="product_description[]" placeholder="Enter Product description" autocomplete="off"></textarea><br></td><td colspan="4"><input type="text" class="form-control" name="project[]" placeholder="Search  Project By Project Name , Clent, Branch" id="project-' + cvalue + '"><input type="hidden" name="inventory_project_id[]" id="project_id-' + cvalue + '" ><input type="hidden" name="client_id[]" id="client_id-' + cvalue + '" ><input type="hidden" name="taxedvalue[]" id="taxedvalue-' + cvalue + '" ><input type="hidden" name="salevalue[]" id="salevalue-' + cvalue + '" ><input type="hidden" name="branch_id[]" id="branch_id-' + cvalue + '" ></td><td colspan="2"><select class="form-control unit 2" data-uid="' + cvalue + '" name="u_m[]" style="display: none"></select></td></tr>`;
    //ajax request
    // $('#saman-row').append(data);
    $('tr.last-item-row').before(data);
    editor();
    row = cvalue;

    $('#productname-' + cvalue).autocomplete({
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
            var t_r = ui.item.data.taxrate;
            var custom = accounting.unformat($("#taxFormat option:selected").val(), accounting.settings.number.decimal);
            if (custom > 0) {
                t_r = custom;
            }
            var discount = ui.item.data.disrate;
            var dup;
            var custom_discount = $('#custom_discount').val();
            if (custom_discount > 0) discount = deciFormat(custom_discount);

            $('#amount-' + id[1]).val(1);
            $('#price-' + id[1]).val(accounting.formatNumber(ui.item.data.price));
            $('#pid-' + id[1]).val(ui.item.data.id);
            $('#vat-' + id[1]).val(accounting.formatNumber(t_r));
            $('#discount-' + id[1]).val(accounting.formatNumber(discount));
            //  $('#dpid-' + id[1]).val(ui.item.data.product_des);
            $('#unit-' + id[1]).val(ui.item.data.unit).attr('attr-org', ui.item.data.unit);
            $('#hsn-' + id[1]).val(ui.item.data.code);
            $('#alert-' + id[1]).val(ui.item.data.alert);
            $('#serial-' + id[1]).val(ui.item.data.serial);
            $('#dpid-' + id[1]).summernote('code', ui.item.data.product_des);
            $("#project-" + id[1]).val(project_description);
            $("#project_id-" + id[1]).val(project_id);
            $("#client_id-" + id[1]).val(customer_id);
            $("#branch_id-" + id[1]).val(branch_id);
            
            rowTotal(cvalue);
            billUpyog();
            if (typeof unit_load === "function") {
                unit_load();
                $('.unit').show();
            }
        },
        create: function (e) {
            $(this).prev('.ui-helper-hidden-accessible').remove();
        }
    });
    $('#project-' + cvalue).autocomplete({
        source: function (request, response) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseurl + 'projects/search/' + billtype,
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
            $('#project_id-' + id[1]).val(ui.item.data.id);
            $('#client_id-' + id[1]).val(ui.item.data.client_id);
            $('#branch_id-' + id[1]).val(ui.item.data.branch_id);
        }
    });

});