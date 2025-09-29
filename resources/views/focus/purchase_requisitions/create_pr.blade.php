@extends ('core.layouts.app')

@section('title', 'Create | Purchase Requisition Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create Purchase Requisition</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.purchase_requisitions.partials.purchase-requisition-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.purchase_requisitions.store', 'method' => 'POST', 'files' => false]) }}
                        @include('focus.purchase_requisitions.form')
                    {{ Form::close() }}
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
            $('.datepicker').datepicker(config.date);
            $('#requisitionsTbl').on('change', '.purchase_qty, .stock_qty', Form.purchaseQtyChange);
            $("#requisitionsTbl").on("click", ".delete", Form.removeRow);
            let rowId = $("#requisitionsTbl tbody tr").length;
            const rowHtml = Form.stockRow();
            // $('.addProduct').addClass('d-none');
            $('#name-p0').autocomplete(Form.autoComp('p0'));
            $('#addProduct').click(function() {
                const i = 'p' + rowId;
                const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
                $("#requisitionsTbl tbody").append(newRowHtml);
                $('#name-' + i).autocomplete(Form.autoComp(i));
                rowId++;
            });
            if (this.purchaseRequest) {
                const request = this.purchaseRequest;
                $('#date').datepicker('setDate', new Date(request.date));
                $('#expect_date').datepicker('setDate', new Date(request.expect_date));
                $('#item_type').prop('disabled',true);
                if(request.item_type == 'project'){
                    $('.div_project').removeClass('d-none');
                    $('.div_milestone').removeClass('d-none');
                    $('.addProduct').addClass('d-none');
                    $('#project').prop('disabled',true);
                    $('#project_milestone').prop('disabled',true);
                    const projectName = "{{ $purchase_request->project? $purchase_request->project->name : '' }}";
                    const projectId = "{{ $purchase_request->project_id }}";
                    if (projectId) $('#project').append(new Option(projectName, projectId, true, true)).change();
                    const milestone_name = "{{ $purchase_request->milestone? $purchase_request->milestone->name : '' }}";
                    const milestone_id = "{{ $purchase_request->project_milestone_id }}";
                    if (milestone_id) $('#project_milestone').append(new Option(milestone_name, milestone_id, true, true)).change();
                }else if(request.item_type == 'finished_goods')
                {
                    $('.div_fg_goods').removeClass('d-none');
                    $('#fg_goods').prop('disabled',true);
                }
            } else {
                $('#user').val('').change();
                $('.datepicker').datepicker('setDate', new Date());
            }
        },

        removeRow(){
            const menu = $(this);
            const row = $(this).parents("tr:first");

            if (menu.is('.delete') && confirm('Are you sure?')) {
                row.remove();
                $('#requisitionsTbl tbody tr.invisible').remove();
            }
        },
        purchaseQtyChange(){
            let el = $(this);
            let row = el.parents('tr:first');
            let qty = accounting.unformat(row.find('.qty').val());
            let purchase_qty = accounting.unformat(row.find('.purchase_qty').val());
            let stock_qty = accounting.unformat(row.find('.stock_qty').val());
            let sum_qty = stock_qty+purchase_qty;
            if(stock_qty > qty){
                // purchase_qty = 0;
                stock_qty = 0;
                // row.find('.purchase_qty').val(purchase_qty)
                row.find('.stock_qty').val(stock_qty)
            }
        },
        stockRow(){
            return `
                <tr id="productRow">
                    <td><span class="numbering">1</span></td>
                    <td><input type="text" name="product_name[]" id="name-p0" class="form-control"></td>
                    <td><select name="unit_id[]" id="uom-p0" class="form-control uom" ></select></td> 
                    <td><span class="code" id="code-p0"></span></td>
                     <td><input type="text" name="remark[]" id="remark-p0" value="" class="form-control remark" ></td>
                    <td><input type="text" name="qty[]" id="qty-p0" value="1" class="form-control qty" ></td>
                    <td><input type="text" name="stock_qty[]" id="stock_qty-p0" value="0" class="form-control stock_qty"></td>
                    <td><input type="text" name="purchase_qty[]" id="purchase_qty-p0" value="0" class="form-control purchase_qty"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p0">
                    <input type="hidden" name="price[]" class="price" id="price-p0">
                    <input type="hidden" name="id[]" class="id" value="0">
                    <input type="hidden" name="milestone_item_id[]" class="milestone_item" id="milestone_item-p0" value="0">
                    <input type="hidden" name="budget_item_id[]" class="budget_item_id" id="budget_item_id-p0" value="0">
                    <input type="hidden" name="purchase_request_item_id[]" class="purchase_request_item_id" value="0">
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
                        if(data.uom){
                            const option = `<option value="${data.uom_id}"  >${data.uom}</option>`;
                            $('#uom-'+i).append(option);
                        }
                    }
                };
        }
    };

    $(() => Form.init());
</script>
@endsection