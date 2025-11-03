@extends ('core.layouts.app')

@section ('title', 'Create Delivery Schedule')

@section('page-header')
    <h1>
        <small>Create Delivery Schedule</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Delivery Schedule</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.delivery_schedules.partials.delivery_schedules-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.delivery_schedules.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.delivery_schedules.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.delivery_schedules.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
        ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            orderSelect: {
                allowClear: true,
                ajax: {
                    url: "{{ route('biller.customer_orders.search') }}",
                    dataType: 'json',
                    type: 'POST',
                    data: ({
                        term
                    }) => ({
                        search: term,
                        customer_id: $("#customer").val()
                    }),
                    processResults: data => {
                        return {
                            results: data.map(v => ({
                                text: v.name,
                                id: v.id,
                            }))
                        }
                    },
                }
            },
       };
       const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                $('#customer').select2({allowClear:true});
                $('#order').select2(config.orderSelect);
                $('#customer').change(this.customerChange);
                $('#order').change(this.orderChange);
                $('#itemsTbl').on('change','.qty',Index.calculateLineTotals)
            },
            customerChange(){
                $('#order').val('').change();
            },
            orderChange(){
                let order_id = $(this).val();
                $.ajax({
                    url: "{{ route('biller.customer_orders.order_items') }}",
                    method: "POST",
                    data: {
                        order_id,
                    },
                    success: function(data){
                        data.forEach((v, i) => {
                            $('#itemsTbl tbody').append(Index.productRow(v, i));
                        });
                    }
                });
            },
            calculateLineTotals() {
                const el = $(this);
                let $row = el.parents('tr:first');
                let qty = parseFloat($row.find('.qty').val()) || 0;
                let rate = parseFloat($row.find('.rate').val()) || 0;
                let vat = parseFloat($row.find('.rowtax').val()) || 0;
                // Subtotal per row (before VAT)
                let subtotal = qty * rate;
                
                // VAT amount per row
                let vatAmount = subtotal * (vat / 100);
                
                // Line total
                let lineTotal = subtotal + vatAmount;

                // Update row
                $row.find('.amt').val(lineTotal.toFixed(2));
                $row.find('.amount').val(lineTotal.toFixed(2));

                return {
                    subtotal,
                    vatAmount,
                    lineTotal
                };
            },

            productRow(v,i){
                return `
                    <tr>
                        <td>${i+1}</td>

                        <td>
                            <input type="text" name="items[${i+1}][product_code]" class="form-control"
                                value="${v.product_code}" readonly>
                        </td>

                        <td>
                            <input type="text" name="items[${i+1}][product_name]" class="form-control"
                                value="${v.product_name}" readonly>
                        </td>

                        <td>
                            <input type="number" name="items[${i+1}][qty]" class="form-control text-end qty"
                                value="${accounting.unformat(v.qty)}">
                        </td>


                        <td>
                            <input type="text" name="items[${i+1}][rate]"
                                class="form-control text-end rate" value="${accounting.unformat(v.rate)}" readonly>
                        </td>

                        <td>
                            <input type="text" name="items[${i+1}][amount]"
                                class="form-control text-end amt" value="${accounting.unformat(v.amount)}" readonly>
                            <input type="hidden" value="${v.itemtax}" class="rowtax">
                            <input type="hidden" name="items[${i+1}][order_item_id]" value="${v.id}">
                            <input type="hidden" name="items[${i+1}][product_id]" value="${v.product_id}">
                            <input type="hidden" name="items[${i+1}][id]" value="${v.id}">
                        </td>
                    </tr>
                `;
            }
       };
       $(()=>Index.init()); 
    </script>
@endsection