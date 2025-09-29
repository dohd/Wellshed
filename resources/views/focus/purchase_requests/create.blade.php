@extends ('core.layouts.app')

@section('title', 'Create | Material Requisition Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Material Requisition Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.purchase_requests.partials.purchase-request-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    {{ Form::open(['route' => 'biller.purchase_requests.store', 'method' => 'POST', 'files' => false]) }}
                        @include('focus.purchase_requests.form')
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

        init() {
            $.ajaxSetup(config.ajax);
            $('#user').select2({allowClear: true});
            $('#reviewer_ids').select2({allowClear: true});
            $('#fg_goods').select2({allowClear: true});
            $('.datepicker').datepicker(config.date)
            // initialize html editor
            editor();

           
            $('#user').val('').change();
            $('.datepicker').datepicker('setDate', new Date());
        
            $('#user').change(this.employeeChange);
            $('#select_user').click(function(){
                if ($(this).is(':checked', true)) {
                    $('.div_reviewer').removeClass('d-none');
                    $('#reviewer_ids').attr('disabled', false);
                } else {
                    $('.div_reviewer').addClass('d-none');
                    $('#reviewer_ids').attr('disabled', true);
                }
            });
            let rowId = 1;
            const rowHtml = Form.stockRow();
            $('.addProduct').addClass('d-none');
            $('#name-p0').autocomplete(Form.autoComp('p0'));
            $('#addProduct').click(function() {
                const i = 'p' + rowId;
                const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
                $("#requisitionsTbl tbody").append(newRowHtml);
                $('#name-' + i).autocomplete(Form.autoComp(i));
                rowId++;
            });
            $("#requisitionsTbl").on("click", ".delete", function() {
                const menu = $(this);
                const row = $(this).parents("tr:first");

                if (menu.is('.delete') && confirm('Are you sure?')) {
                    row.remove();
                    $('#requisitionsTbl tbody tr.invisible').remove();
                }
            });

            $('#requisitionsTbl').on('change', '.qty, .uom', function() {
                const el = $(this);
                const row = el.parents('tr:first');

                const qty = accounting.unformat(row.find('.qty').val());
                const milestone_qty = accounting.unformat(row.find('.milestone_qty').val());
                const qty_requested = accounting.unformat(row.find('.qty_requested').val());
                let total_qty = qty + qty_requested;
                
                if($('#item_type').val() == 'project' || $('#item_type').val() == 'finished_goods'){
                    if(milestone_qty < total_qty){
                        let qty_diff = milestone_qty - qty_requested;
                        row.find('.qty').val(qty_diff);
                    }else{
                        row.find('.qty').val(qty);
                    }
                }
                const price = accounting.unformat(row.find('.price').val());

                row.find('.price').val(accounting.formatNumber(price));
               

                if (el.is('.uom')) {
                    const purchasePrice = el.find('option:selected').attr('purchase_price');
                    row.find('.price').val(purchasePrice).change();
                }
            });

            const projectUrl = "{{ route('biller.projects.project_search') }}";
            function projectData(data) {
                data = data.map(v => ({id: v.id, text: v.name, budget: v.budget ? v.budget.budget_total : 0 }));
                loadedProjectDetails = data;
                return {results: data};
            }
            $("#project").select2(select2Config(projectUrl, projectData));
            function select2Config(url, callback) {
                return {
                    ajax: {
                        url,
                        dataType: 'json',
                        type: 'POST',
                        quietMillis: 50,
                        data: ({term}) => ({q: term, keyword: term}),
                        processResults: callback
                    },
                    allowClear: true,
                }
            }
            function select2Config2(url, callback) {
                return select2Config(url, callback);
            }
            $('#project').change(this.projectChange);
            $('#item_type').change(this.itemTypeChange);
            $('#project_milestone').change(this.projectMilestoneChange);
            $('#fg_goods').change(this.finishedGoodsChange);
        },
        projectChange(){
            const project_id = $('#project').val();
            let select = $('#project_milestone');
            $.ajax({
                url: "{{ route('biller.getProjectMileStones') }}",
                method: 'GET',
                data: { projectId: project_id },
                dataType: 'json',
                success: function(data) {
                    select.empty();
                    if(data.length === 0){
                        select.append($('<option>', {
                            value: null,
                            text: 'No Milestones Created For This Project'
                        }));
                        select.append($('<option>', {
                            value: 0,
                            text: 'Unallocated Milestone'
                        }));
                    } else {
                        select.append($('<option>', {
                            value: null,
                            text: 'Select a Budget Line'
                        }));
                        select.append($('<option>', {
                            value: 0,
                            text: 'Unallocated Milestone'
                        }));
                        // Add new options based on the received data
                        for (var i = 0; i < data.length; i++) {
                            const options = { year: 'numeric', month: 'short', day: 'numeric' };
                            const date = new Date(data[i].end_date);
                            select.append($('<option>', {
                                value: data[i].id,
                                text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                            }));
                        }
                    }

                },
                error: function() {
                    console.log('Error loading data');
                }
            });
        },

        itemTypeChange(){
            const item_type = $('#item_type').val();
            $('#requisitionsTbl tbody').html('');
            if (item_type == 'stock'){
                $('.div_project').addClass('d-none');
                $('.div_milestone').addClass('d-none');
                $('.div_fg_goods').addClass('d-none');
                $('#project').prop('disabled',true);
                $('#project_milestone').val(0);
                $('.addProduct').removeClass('d-none');
                $('#requisitionsTbl tbody').append(Form.stockRow());
                $('#name-p0').autocomplete(Form.autoComp('p0'));
            }else if(item_type == 'project')
            {
                $('.div_project').removeClass('d-none');
                $('.div_milestone').removeClass('d-none');
                $('.div_fg_goods').addClass('d-none');
                $('.addProduct').addClass('d-none');
                $('#project').prop('disabled',false);
            }else if(item_type == 'finished_goods')
            {
                $('.div_project').addClass('d-none');
                $('.div_milestone').addClass('d-none');
                $('.div_fg_goods').removeClass('d-none');
                $('#project').prop('disabled',true);
                $('#project_milestone').val(0);
            }
        },
        projectMilestoneChange(){
            const milestone_id = $('#project_milestone').val();
            const project_id = $('#project').val();
            $('#requisitionsTbl tbody').html('');
            $.ajax({
                url: "{{route('biller.milestones.get_milestone_items')}}",
                method: 'POST',
                data: {
                    milestone_id : milestone_id,
                    project_id : project_id,
                },
                success: function (data){
                    data.forEach((v,i) => {
                         $('#requisitionsTbl tbody').append(Form.productRow(v,i));
                    });
                }
            });
        },
        finishedGoodsChange(){
            const finished_good_id = $('#fg_goods').val();
            $('#requisitionsTbl tbody').html('');
            $.ajax({
                url: "{{route('biller.parts.get_items')}}",
                method: 'POST',
                data: {
                    finished_good_id : finished_good_id,
                },
                success: function (data){
                    data.forEach((v,i) => {
                         $('#requisitionsTbl tbody').append(Form.productRow(v,i));
                    });
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
                    <td><input type="text" id="qty_requested-p0" class="form-control" readonly></td>
                    <td><input type="text" id="milestone_qty-p0" value="0" class="form-control" readonly></td>
                    <td><input type="text" name="qty[]" id="qty-p0" class="form-control"></td>
                    <td><input type="text" name="remark[]" id="remark-p0" value="" class="form-control remark"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p0">
                    <input type="hidden" name="price[]" class="price" id="price-p0">
                    <input type="hidden" name="id[]" class="id" value="0">
                    <input type="hidden" name="milestone_item_id[]" class="milestone_item" id="milestone_item-p0" value="0">
                    <input type="hidden" name="budget_item_id[]" class="budget_item_id" id="budget_item_id-p0" value="0">
                    <input type="hidden" name="part_item_id[]" class="part_item_id" id="part_item_id-p0" value="">
                </tr>
            `;
        },
        productRow(v,i){
            return `
                <tr>
                    <td><span class="numbering">${i+1}</span></td>
                    <td><input type="text" name="product_name[]" id="name-p0" value="${v.product_name}" class="form-control" readonly></td>
                    <td><select name="unit_id[]" id="uom-p0" class="form-control uom">
                        <option value="${v.unit_id}" selected>${v.uom}</option>
                        </select></td> 
                    <td><span class="code" id="code-p0">${v.code}</span></td>
                    <td><input type="text" id="qty_requested-p0" value="${v.qty_requested}" class="form-control qty_requested" readonly></td>
                    <td><input type="text" id="milestone_qty-p0" value="${v.qty}" class="form-control milestone_qty" readonly></td>
                    <td><input type="text" name="qty[]" id="qty-p0" value="0" class="form-control qty"></td>
                    <td><input type="text" name="remark[]" id="remark-p0" value="" class="form-control remark"></td>
                    <td><button type="button" class="btn btn-danger delete"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <input type="hidden" name="product_id[]" id="productid-p0" value="${v.product_id}">
                    <input type="hidden" name="price[]" class="price" id="price-p0" value="${v.price}">
                    <input type="hidden" name="milestone_item_id[]" class="milestone_item" id="milestone_item-p0" value="${v.milestone_item_id}">
                    <input type="hidden" name="budget_item_id[]" class="budget_item_id" id="budget_item_id-p0" value="${v.budget_item_id}">
                    <input type="hidden" name="part_item_id[]" class="part_item_id" id="part_item_id-p0" value="${v.part_item_id}">
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