@extends ('core.layouts.app')

@section('title', 'Create Delivery')

@section('page-header')
    <h1>
        <small>Create Delivery</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Delivery</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.deliveries.partials.deliveries-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.deliveries.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include('focus.deliveries.form')
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.deliveries.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
                    url: "{{ route('biller.customer_orders.select') }}",
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
                                driver_id: v.driver_id
                            }))
                        }
                    },
                },
                templateSelection: formatOrderSelection
            },
        };
        function formatOrderSelection(order) {
            if (!order.id) return order.text;
            // attach driver_id as a data attribute for later use
            $('#order').attr('data-driver-id', order.driver_id || '');
            return order.text;
        }

        const Index = {
            init() {
                $.ajaxSetup(config.ajax);
                $('#customer').select2({
                    allowClear: true
                }).change(Index.customerChange);
                $('#order').select2(config.orderSelect);
                $('#delivery_schedule').select2({
                    allowClear: true
                });
                $('#driver').select2({
                    allowClear: true
                });
                $('#order').change(Index.orderChange);
                $('#delivery_schedule').change(Index.deliveryScheduleChange);
            },
            customerChange(){
                var select = $('#order');
                // Clear any existing options
                select.empty();
                $('#order').select2(config.orderSelect);
            },
            deliveryScheduleChange(){
                let delivery_schedule_id = $(this).val();
                $('#itemsTbl tbody').html('');
                $.ajax({
                    url: "{{ route('biller.delivery_schedules.get_schedule_items') }}",
                    method: "POST",
                    data: {
                        delivery_schedule_id: delivery_schedule_id
                    },
                    success: function(data) {
                        console.log(data);
                        data.forEach((v, i) => {
                            $('#itemsTbl tbody').append(Index.productRow(v, i));
                        });
                    }
                });
            },
            productRow(v, i) {
                return `
                    <tr>
                        <td>
                            <select name="product_id[]" id="product_id-0" class="form-control product_id"
                                data-placeholder="Search Product">
                                <option value="${v.product_id}">${v.product.name}</option>
                            </select>
                        </td>
                        <td><input type="number" step="0.01" name="planned_qty[]" class="form-control planned_qty" id="planned_qty-0"
                            value="${v.qty}" placeholder="0.00" readonly></td>
                        <td><input type="number" step="0.01" name="delivered_qty[]" class="form-control delivered_qty" id="delivered_qty-0"
                                placeholder="0.00" value="0"></td>
                        <td><input type="number" step="0.01" name="returned_qty[]" class="form-control returned_qty" id="returned_qty-0"
                                placeholder="0.00" value="0"></td>
                        <td><input type="number" step="0.01" name="remaining_qty[]" class="form-control remaining_qty" id="remaining_qty-0"
                                placeholder="0.00" value="0" readonly></td>
                        
                        
                        <input type="hidden" name="cost_of_bottle[]" class="cost_of_bottle" value="${v.cost_of_bottle}">
                        <input type="hidden" name="cost_of_remaining[]" class="cost_of_remaining" value="0">
                        <input type="hidden" name="cost_of_returned[]" class="cost_of_returned" value="0">
                        <input type="hidden" name="amount[]" class="amount" value="0">
                        <input type="hidden" name="rate[]" class="rate" value="${v.rate}">
                        <input type="hidden" name="delivery_schedule_item_id[]" class="delivery_schedule_item_id" value="${v.delivery_schedule_item_id}">
                        <input type="hidden" name="id[]" value="0">
                    </tr>
                `;
            },
            orderChange() {
                let order_id = $(this).val();
                let selectedData = $('#order').select2('data')[0];
                $('#driver').val(selectedData.driver_id).trigger('change');

                $.ajax({
                    url: "{{ route('biller.delivery_schedules.get_schedules') }}",
                    method: "POST",
                    data: {
                        order_id: order_id
                    },
                    success: function(data) {
                        var select = $('#delivery_schedule');
                        // Clear any existing options
                        select.empty();
                        if (data.length === 0) {
                            select.append($('<option>', {
                                value: null,
                                text: 'No Schedule'
                            }));
                        } else {
                            select.append($('<option>', {
                                value: null,
                                text: 'Search Delivery Schedule'
                            }));
                            // Add new options based on the received data
                            for (var i = 0; i < data.length; i++) {

                                select.append($('<option>', {
                                    value: data[i].id,
                                    text: data[i].name
                                }));
                            }
                            
                        }
                    }
                });
            }
        };
        $(() => Index.init());
    </script>
@endsection
