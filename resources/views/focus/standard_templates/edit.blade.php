@extends ('core.layouts.app')

@section ('title', 'Edit Standard Templates')

@section('page-header')
    <h1>
        Finished Product Standard Template
        <small>Edit Standard Templates</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Standard Templates</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.standard_templates.partials.standard_templates-header-buttons')
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
                                    {{ Form::model($standard_template, ['route' => ['biller.standard_templates.update', $standard_template], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.standard_templates.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.standard_templates.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->

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
        purchaseRequest: @json(@$purchase_request),

        init() {
            $.ajaxSetup(config.ajax);
            $('#user').select2({allowClear: true});
            $('.datepicker').datepicker(config.date)
            // initialize html editor
            editor();

            const rowHtml = Form.stockRow();
            $("#productRow").remove();
            let rowId = $("#standard_templatesTbl tbody tr").length;
            $('#addProduct').click(function() {
                const i = 'p' + rowId;
                const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
                $("#standard_templatesTbl tbody").append(newRowHtml);
                $('#name-' + i).autocomplete(Form.autoComp(i));
                rowId++;
            });

            $("#standard_templatesTbl").on("click", ".delete", function() {
                const menu = $(this);
                const row = $(this).parents("tr:first");

                if (menu.is('.delete') && confirm('Are you sure?')) {
                    row.remove();
                    $('#standard_templatesTbl tbody tr.invisible').remove();
                }
            });
            
        },
        
        stockRow(){
            return `
                 <tr id="productRow">
                    <td><span class="numbering">1</span></td>
                    <td><input type="text" name="product_name[]" id="name-p0" class="form-control"></td>
                    <td><select name="unit_id[]" id="uom-p0" class="form-control uom" ></select></td> 
                    <td><span class="code" id="code-p0"></span></td>
                    <td><input type="text" name="qty[]" id="qty-p0" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p0">
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
                        $('#qty-' + i).val(1);
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