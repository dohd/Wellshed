@extends ('core.layouts.app')

@section ('title', 'Create Petty Cash')

@section('page-header')
    <h1>
        <small>Create Petty Cash</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Petty Cash</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.petty_cashs.partials.petty_cashs-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.petty_cashs.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.petty_cashs.form")
                                        
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
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };
        const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#purchase_requisition').select2({allowClear: true});
                $('#employee').select2({allowClear: true});
                $('#casual').select2({allowClear: true});
                $('#third_party_user').select2({allowClear: true});
                $('#user').select2({allowClear: true});
                $('#purchase_requisition').change(this.purchaseRequisitionChange);
                $('#stockTbl').on('change','.check', this.stockChange);
                $('#stockTbl').on('input', '.qty, .rowtax, .price', this.calculateRow);
                $('#tax').on('change', function () {
                    var selectedTax = $(this).val(); // get selected tax value

                    // Loop through each .rowtax dropdown and set the selected option
                    $('.rowtax').each(function () {
                        $(this).val(selectedTax).trigger('change'); // set value and trigger change if needed
                        Index.calcRow($(this).parents('tr:first'));
                    });
                });
                $('#item_type').change(function(){
                    let item_type = $(this).val();
                    if(item_type == 'others'){
                        $('#purchase_requisition').attr('disabled',true);

                    }else{
                        $('#purchase_requisition').attr('disabled',false);
                    }
                });
                $('#user_type').change(function(){
                    let user_type = $(this).val();
                    if(user_type == 'employee'){
                        $('.div_casual').addClass('d-none');
                        $('.div_employee').removeClass('d-none');
                        $('.div_third_party_user').addClass('d-none');
                        //disabled
                        $('#employee').attr('disabled',false);
                        $('#casual').attr('disabled',true);
                        $('#third_party_user').attr('disabled',true);
                    }else if(user_type == 'casual')
                    {
                        $('.div_casual').removeClass('d-none');
                        $('.div_employee').addClass('d-none');
                        $('.div_third_party_user').addClass('d-none');
                        //disabled
                        $('#employee').attr('disabled',true);
                        $('#third_party_user').attr('disabled',true);
                        $('#casual').attr('disabled',false);

                    }else if(user_type == 'third_party_user')
                    {
                        $('.div_casual').addClass('d-none');
                        $('.div_employee').addClass('d-none');
                        $('.div_third_party_user').removeClass('d-none');
                        //disabled
                        $('#employee').attr('disabled',true);
                        $('#casual').attr('disabled',true);
                        $('#third_party_user').attr('disabled',false);
                    }
                });
                let docRowId = 1;
                const docRow = $('#stockTbl tbody tr').html();
                $('#product_name-0').autocomplete(Index.autoComp('0'));
                $('#addDoc').click(function() {
                    let i = docRowId;
                    let html = docRow.replace(/-0/g, '-'+docRowId);
                    $('#stockTbl tbody').append('<tr>' + html + '</tr>');
                    $('#product_name-' + i).autocomplete(Index.autoComp(i));
                    docRowId++;
                });
                // remove schedule row
                $('#stockTbl').on('click', '.remove_doc', function() {
                    $(this).parents('tr').remove();
                    docRowId--;
                    Index.calcRow($(this).parents('tr:first'));
                });
            },
            stockChange(){
                const row = $(this).closest('tr');
                const isChecked = $(this).is(':checked');

                row.find('input[type="text"],input[type="hidden"], select').prop('disabled', !isChecked);

                if (isChecked) {
                    Index.calcRow(row);
                } else {
                    row.find('.amount').text('0.00');
                    row.find('.taxable, .rate_tax, .row_amount').val('0.00');
                    Index.calculateTotal();
                }
            },


            purchaseRequisitionChange(){
                const pr_id = $(this).val();
                $('#stockTbl tbody').html('');
                $.ajax({
                    url: "{{ route('biller.purchase_requisitions.get_items')}}",
                    method: "POST",
                    data: {
                        purchase_requisition_id: pr_id
                    },
                    success: function(data){
                        data.items.forEach((v,i) => {
                            $('#stockTbl tbody').append(Index.stockProductRow(v,i));
                        });
                    }
                });
            },
            stockProductRow(v,i){
                return `
                    <tr>
                        <td><input type="checkbox" class="check"></td>
                        <td><input type="text" name="product_name[]" value="${v.product_name}" id="product_name-${i+1}" class="form-control product_name" disabled></td>
                        <td>
                            <select name="uom[]" id="uom-0" class="form-control uom" disabled>
                                <option value="${v.uom}">${v.uom}</option>
                            </select>
                        </td>
                        <td><input type="text" name="qty[]" value="${accounting.unformat(v.qty)}" id="qty-${i+1}" class="form-control qty" disabled></td>
                        <td>
                            <select class="form-control rowtax" name="itemtax[]" id="rowtax-${i+1}" disabled>
                                @foreach ($additionals as $tax)
                                    <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                        {{ $tax->name }}
                                    </option>
                                @endforeach                                                    
                            </select>
                        </td>
                        <td><input type="text" name="price[]" value="${accounting.unformat(v.price)}" id="price-${i+1}" class="form-control price" disabled></td>
                        <td><span class="amount">0</span></td>
                        <input type="hidden" class="rate_tax" name="tax_rate[]" disabled>
                        <input type="hidden" class="row_amount" name="amount[]" disabled>
                        <input type="hidden" class="form-control taxable" value="0">
                        <input type="hidden" class="form-control product_id" name="product_id[]" value="${v.product_id}" disabled>
                    </tr>
                `;
            },
            calculateRow(){
                const row = $(this).closest('tr');
                const qty = accounting.unformat(row.find('.qty').val()) || 0;
                const price = accounting.unformat(row.find('.price').val()) || 0;
                const rowtax = 1 + row.find('.rowtax').val()/100;
                const amount = qty * price * rowtax;
                const taxable = qty * price * (rowtax - 1);

                // row.find('.price').val(accounting.formatNumber(price));
                row.find('.amount').text(accounting.formatNumber(amount));
                row.find('.taxable').val(accounting.formatNumber(taxable));
                row.find('.rate_tax').val(accounting.formatNumber(taxable));
                row.find('.row_amount').val(accounting.formatNumber(amount));

                Index.calculateTotal();
            },

            calcRow(row){
                const qty = accounting.unformat(row.find('.qty').val()) || 0;
                const price = accounting.unformat(row.find('.price').val()) || 0;
                const rowtax = 1 + (accounting.unformat(row.find('.rowtax').val()) || 0) / 100;
                const amount = qty * price * rowtax;
                const taxable = qty * price * (rowtax - 1);

                // row.find('.price').val(accounting.formatNumber(price));
                row.find('.amount').text(accounting.formatNumber(amount));
                row.find('.taxable').val(accounting.formatNumber(taxable));
                row.find('.rate_tax').val(accounting.formatNumber(taxable));
                row.find('.row_amount').val(accounting.formatNumber(amount));

                Index.calculateTotal();
            },


            calculateTotal(){
                let tax = 0;
                let grandTotal = 0;

                $('#stockTbl tbody tr').each(function() {
                    const row = $(this);
                    
                    // Skip the row if inputs are disabled (check any one field, like qty)
                    if (row.find('.qty').is(':disabled')) return;

                    const qty = accounting.unformat(row.find('.qty').val()) || 0;
                    const price = accounting.unformat(row.find('.price').val()) || 0;
                    const rowtax = (parseFloat(row.find('.rowtax').val()) || 0) / 100 + 1;

                    const amount = qty * price * rowtax;
                    const taxable = amount - (qty * price);
                    
                    tax += taxable;
                    grandTotal += amount;
                });

                $('#tax_amount').val(accounting.formatNumber(tax));
                $('#total').val(accounting.formatNumber(grandTotal));
                $('#subtotal').val(accounting.formatNumber(grandTotal - tax));
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
                        {
                            data.units.forEach(v => {
                                const rate = accounting.unformat(v.base_ratio) * purchasePrice;
                                const option = `<option value="${v.code}" purchase_price="${rate}" >${v.code}</option>`;
                                $('#uom-'+i).append(option);
                            });
                        }
                        else if(data.uom){
                            const option = `<option value="${data.uom}"  >${data.uom}</option>`;
                            $('#uom-'+i).append(option);
                        }
                         Index.calcRow($(this).parents('tr:first'));
                    }
                };
            }

        };
        $(() => Index.init());
    </script>
@endsection