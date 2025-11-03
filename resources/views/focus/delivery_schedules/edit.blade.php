@extends ('core.layouts.app')

@section ('title', 'Edit Delivery Schedule')

@section('page-header')
    <h1>
        <small>Edit Delivery Schedule</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Delivery Schedule</h4>

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
                                    {{ Form::model($delivery_schedule, ['route' => ['biller.delivery_schedules.update', $delivery_schedule], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.delivery_schedules.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.delivery_schedules.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
                $('#customer').select2({allowClear:true}).change();
                $('#order').select2(config.orderSelect);
                $('#customer').change(this.customerChange);
                const delivery_schedule = @json($delivery_schedule);
                if(delivery_schedule && delivery_schedule.id){
                    $('#customer').attr('disabled',true)
                    $('#order').attr('disabled',true)
                }
                $('#itemsTbl').on('change','.qty',Index.calculateLineTotals)
            },
            customerChange(){
                $('#order').select2(config.orderSelect);
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
       };
       $(()=>Index.init()); 
    </script>
@endsection