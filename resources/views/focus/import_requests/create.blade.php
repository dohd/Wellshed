@extends ('core.layouts.app')

@section ('title', 'Create Import Request')

@section('page-header')
    <h1>
        <small>Create Import Request</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Import Request</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.import_requests.partials.import_requests-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.import_requests.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.import_requests.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.import_requests.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
    <script>
        const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
            select2: {allowClear: true},
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                $('.datepicker').datepicker(config.date)
                $('.datepicker').datepicker('setDate', new Date());
                $('#supplier').select2(config.select2);
                $('#purchase_requisition_ids').select2(config.select2);
                $('#supplier').change(this.supplierChange);
                let rowId = 1;
                const rowHtml = Index.stockRow();
                // $('.addProduct').addClass('d-none');
                $('#name-p0').autocomplete(Index.autoComp('p0'));
                $('#addProduct').click(function() {
                    const i = 'p' + rowId;
                    const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
                    $("#importsTbl tbody").append(newRowHtml);
                    $('#name-' + i).autocomplete(Index.autoComp(i));
                    rowId++;
                });
                $("#importsTbl").on("click", ".delete", function() {
                    const menu = $(this);
                    const row = $(this).parents("tr:first");

                    if (menu.is('.delete') && confirm('Are you sure?')) {
                        row.remove();
                        $('#importsTbl tbody tr.invisible').remove();
                    }
                });
                $('#btnSubmit').on('click', this.onSearchPRItems);
            },
            onSearchPRItems(){
                let purchase_requisition_ids = $('#purchase_requisition_ids').val();
                $('#importsTbl tbody').html('');
                $.ajax({
                    url: "{{ route('biller.purchase_requisitions.items') }}", // Your Laravel route
                    type: "POST",
                    data: {
                        purchase_requisition_ids: purchase_requisition_ids,
                        _token: "{{ csrf_token() }}" // CSRF Token for security
                    },
                    success: function (response) {
                        // alert(response.message);
                        response.forEach((v,i) => {
                            $('#importsTbl tbody').append(Index.itemsRow(v,i));
                        });
                        // $('#importsTbl tbody').append(Index.addRows());
                    },
                    error: function (xhr) {
                        alert("Something went wrong!");
                    }
                });
            },

            supplierChange(){
                const supplier_id = $(this).val();
                const currency_id = $('#supplier option:selected').attr('currencyId');
                const currency_rate = $('#supplier option:selected').attr('currency_rate');
                const currency_code = $('#supplier option:selected').attr('currency_code');
                const rate = accounting.unformat(currency_rate);
                $('#currency').html(`<option value="${currency_id}" rate="${rate}">${currency_code || ''}</option>`);
                $('#currency').val(currency_id);
                $('#fx_curr_rate').val(rate);
            },
            stockRow(){
                return `
                    <tr>
                        <td><span class="numbering"></span></td>
                        <td><input type="text" name="product_name[]" id="name-p0" value="" class="form-control"></td>
                        <td><input name="unit[]" id="uom-p0" class="form-control uom" />
                        </td> 
                        <td><input type="text" name="qty[]" id="qty-p0" value="" class="form-control qty"></td>
                        <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                        <input type="hidden" name="product_id[]" id="productid-p0" value="0">
                        <input type="hidden" name="id[]" class="id" value="0"> 
                    </tr>
                `;
            },
            itemsRow(v,i){
                return `
                    <tr>
                        <td><span class="numbering"></span></td>
                        <td><input type="text" name="product_name[]" id="name-p0" value="${v.product_name}" class="form-control"></td>
                        <td><input name="unit[]" value="${v.uom}" id="uom-p0" class="form-control uom" />
                        </td> 
                        <td><input type="text" value="${v.qty}" name="qty[]" id="qty-p0" value="" class="form-control qty"></td>
                        <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                        <input type="hidden" name="product_id[]" value="${v.product_id}" id="productid-p0" value="0">
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
                        if(data.uom){
                            $('#uom-'+i).val(data.uom);
                        }
                    }
                };
            }
        };
        $(()=>Index.init())
    </script>
@endsection