@extends ('core.layouts.app')

@section ('title', 'Create Parts')

@section('page-header')
    <h1>
        Finished Product Part
        <small>Create Parts</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Parts</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.parts.partials.parts-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    {{ Form::open(['route' => 'biller.parts.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.parts.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.parts.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!-- form-group -->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Form = {

        init() {
            $.ajaxSetup(config.ajax);
            $('#user').select2({allowClear: true});
            $('#template').select2({allowClear: true});
            $('.datepicker').datepicker(config.date)
            // initialize html editor
            editor();

           
            $('#user').val('').change();
            $('.datepicker').datepicker('setDate', new Date());
        
            $('#user').change(this.employeeChange);
            let rowId = 1;
            const rowHtml = Form.stockRow();
         
            $('#name-p0').autocomplete(Form.autoComp('p0'));
            $('#addProduct').click(function() {
                const i = 'p' + rowId;
                const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
                $("#partsTbl tbody").append(newRowHtml);
                $('#name-' + i).autocomplete(Form.autoComp(i));
                rowId++;
            });
            $("#partsTbl").on("click", ".delete", function() {
                const menu = $(this);
                const row = $(this).parents("tr:first");

                if (menu.is('.delete') && confirm('Are you sure?')) {
                    row.remove();
                    $('#partsTbl tbody tr.invisible').remove();
                }
            });

            $('#type').change(this.typeChange);
            $('#template').change(this.templateChange);
            $('#total').change(Form.totalQtyChange);
            $('#partsTbl').on('change', '.qty_for_single', Form.onQtyChange);
            Form.calculateQty();

           
        },

        onQtyChange(){
            const el = $(this);
            const row = el.parents('tr:first');
            const total_qty = $('#total').val();
            let row_qty = row.find('.qty_for_single').val();
            let qty = total_qty * row_qty;
            row.find('.qty').val(qty);
        },

        totalQtyChange(){
            Form.calculateQty();
        },

        calculateQty(){
            const totalQty = $('#total').val();
            $("#partsTbl tbody tr").each(function(i) {
                const qty = $(this).find('.qty_for_single').val();
                let quantity = qty*totalQty;
                $(this).find('.qty').val(quantity);
            });
        },

        templateChange(){
            const template_id = $('#template').val();
            $('#partsTbl tbody').html('');
            $.ajax({
                url : "{{route('biller.standard_templates.get_std_templates')}}",
                method : 'POST',
                data: {
                    template_id: template_id
                },
                success: function(data){
                    console.log(data)
                    data.forEach((v,i) => {
                         $('#partsTbl tbody').append(Form.productRow(v,i));
                    });
                    Form.calculateQty();
                }
            });
        },

        typeChange(){
            const type = $(this).val();
            if(type == 'no'){
                $('.div_template').addClass('d-none');
                $('#template').val('');
            }else if(type == 'yes'){
                $('.div_template').removeClass('d-none');
                // $('#template').val('');
            }
        },
        
        stockRow(){
            return `
                <tr id="productRow">
                    <td><span class="numbering">1</span></td>
                    <td><input type="text" name="product_name[]" id="name-p0" class="form-control"></td>
                    <td><select name="unit_id[]" id="uom-p0" class="form-control uom" ></select></td> 
                    <td><span class="code" id="code-p0"></span></td>
                    <td><input type="text" name="qty_for_single[]" id="qty_for_single-p0" class="form-control qty_for_single"></td>
                    <td><input type="text" name="qty[]" id="qty-p0" class="form-control qty"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p0">
                    <input type="hidden" name="id[]" class="id" value="0">
                    
                </tr>
            `;
        },
        productRow(v,i){
            return `
                <tr id="productRow">
                    <td><span class="numbering">${i+1}</span></td>
                    <td><input type="text" name="product_name[]" id="name-p0" value="${v.product_name}" class="form-control"></td>
                    <td><select name="unit_id[]" id="uom-p0" class="form-control uom" >
                        <option value="${v.unit_id}" selected>${v.uom}</option>
                        </select>
                    </td> 
                    <td><span class="code" id="code-p0">${v.code}</span></td>
                    <td><input type="text" name="qty_for_single[]" id="qty_for_single-p0" value="${v.qty}" class="form-control qty_for_single"></td>
                    <td><input type="text" name="qty[]" id="qty-p0" value="${v.qty}" class="form-control qty" readonly></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p0" value="${v.product_id}>
                    <input type="hidden" name="id[]" class="id" value="0">
                    
                </tr>
            `;
        },
       
        autoComp(i) {
                return {
                    source: function(request, response) {
                        // stock product
                        let term = request.term;
                        let url = "{{ route('biller.products.purchase_search') }}";
                        let data = {
                            keyword: term,
                            price_customer_id: $('#price_customer').val(),
                        };
                        $.ajax({
                            url,
                            data,
                            method: 'POST',
                            success: result => response(result.map(v => ({
                                label: v.name,
                                value: v.name,
                                data: v
                            }))),
                        });
                    },
                    autoFocus: true,
                    minLength: 0,
                    select: function(event, ui) {
                        const {
                            data
                        } = ui.item;


                        const row = $(this).parents("tr:first");

                        $('#productid-' + i).val(data.id);
                        $('#name-' + i).val(data.name);
                        $('#available_qty-' + i).val(data.qty);
                        $('#code-' + i).text(data.code);
                        $('#qty_for_single-' + i).val(1);
                        $('#uom-'+i).html('');
                        let purchasePrice = +data.purchase_price;
                        $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();
                        if(data.units)
                            data.units.forEach(v => {
                                const rate = accounting.unformat(v.base_ratio) * purchasePrice;
                                const option = `<option value="${v.id}" purchase_price="${rate}" >${v.code}</option>`;
                                $('#uom-'+i).append(option);
                            });
                    }
                };
            }
    };

    $(() => Form.init());
</script>
@endsection