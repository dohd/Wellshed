{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        datepicker: {
            autoHide: true,
            format: "{{config('core.user_date_format')}}"
        },
    };
   
    const Form = {
        init() {
            $('.datepicker').datepicker(config.datepicker);
            $('#unit, #compound_unit, #product_cat, #asset_account, #exp_account, #ref').select2({allowClear: true});

            $('#name').keyup(() => {
                $('.var-name:first').val($('#name').val())
            });
            $('#unit').change(Form.unitChange);
            $('#itemType').change(Form.changeItemType).change();
            $('#type').change(Form.typeChange).change();
            const events = [".add_more", ".add_serial", ".v_delete", ".v_delete_temp", ".v_delete_serial"];
            const handlers = [Form.addMore, Form.addSerial, Form.delVariableProduct, Form.delProduct, Form.delSerial];
            events.forEach((v,i) => $(document).on('click', v, handlers[i]));
        },

        typeChange(){
            let type = $('#type').val();
            if(type == 'empty'){
                $('#ref').prop('disabled',false);
            }else if(type == 'full'){
                $('#ref').prop('disabled',true);

            }
        },

        changeItemType() {
            // service product
            if ($(this).val() == 3) {
                $('#asset_account, #exp_account').removeAttr('required');
                $('.location').removeAttr('required');
            } else {
                $('#asset_account, #exp_account').attr('required', true);
                $('.location').attr('required', true);
            }
        },

        unitChange() {
            const optionData = @json($productvariables->where('unit_type', 'compound')->values());
            if (optionData.length) {
                $('#compound_unit').empty();
                optionData.forEach(v => {
                    if (v.base_unit_id != $('#unit').val()) return;
                    $('#compound_unit').append(new Option(v.code, v.id, false, false));
                })
                $('#compound_unit').change();
            }
        },

        addMore(e) {
            e.preventDefault();
            var product_details = $('#main_product').clone().find(".old_id input:hidden").val(0).end();
            product_details.find(".del_b").append('<button class="btn btn-danger v_delete_temp m-1 align-content-end"><i class="fa fa-trash"></i> </button>').end();
            $('#added_product').append(product_details);
            $('.datepicker').datepicker(config.datepicker);
        },

        delVariableProduct(e) {
            e.preventDefault();
            var p_v = $(this).closest('div').attr('data-vid');
            $('#remove_variation').append("<input type='hidden' name='remove_v[]' value='" + p_v + "'>");
            alert("{{trans('products.alert_removed')}}");
            $('#pv_' + p_v).remove();        
        },

        delProduct(e) {
            e.preventDefault();
            $(this).closest('div .product').remove();
        },

        addSerial(e) {
            e.preventDefault();
            $('#added_product').append(
                `<div class="form-group serial"><label for="field_s" class="col-lg-2 control-label">{{trans('products.product_serial')}}</label><div class="col-lg-10">
                <input class="form-control box-size" placeholder="{{trans('products.product_serial')}}" name="product_serial[]" type="text"  value=""></div>
                <button class="btn-sm btn-purple v_delete_serial m-1 align-content-end"><i class="fa fa-trash"></i> </button></div>`
            );
        },

        delSerial(e) {
            e.preventDefault();
            $(this).closest('div .serial').remove();
        },
    };

    $(Form.init);
</script>