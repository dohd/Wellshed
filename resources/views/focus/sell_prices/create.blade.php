@extends ('core.layouts.app')

@section ('title', 'Create Selling Price Costing')

@section('page-header')
    <h1>
        <small>Create Selling Price Costing</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Selling Price Costing</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.sell_prices.partials.sell_prices-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.sell_prices.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-sp']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.sell_prices.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.sell_prices.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
        };
        const Index = {
            previousType : $('#type').val(),
            previousPercentFixed : $('#percent_fixed_value').val(),
            previousRecommendedType : $('#recommend_type').val(),
            previousRecommendedPercentFixed : $('#recommended_value').val(),
            init(){
                $.ajaxSetup(config.ajax);
                $('#import_request').select2({allowClear: true});
                $('#import_request').change(this.importChange)

                $(document).on('focus', '#type, #percent_fixed_value', function () {
                    Index.previousType = $('#type').val();
                    Index.previousPercentFixed = $('#percent_fixed_value').val();
                });
                $(document).on('change', '#type, #percent_fixed_value', this.typeChange);
                $(document).on('focus', '#recommend_type, #recommended_value', function () {
                    Index.previousRecommendedType = $('#recommend_type').val();
                    Index.previousRecommendedPercentFixed = $('#recommended_value').val();
                });
                $(document).on('change', '#recommend_type, #recommended_value', this.recommendedTypeChange);
            },

            recommendedTypeChange(){
                if(confirm('All Recommended Prices will be Recalculated')){
                    const type = $('#recommend_type').val();
                    const recommended_value = accounting.unformat($('#recommended_value').val());
                    let value = 0
                    if(type == 'percentage'){
                        $('#productTbl tbody tr').each(function(){
                            const row = $(this);
                            const minimum_selling_price = accounting.unformat(row.find('.minimum_selling_price').val() || 0);
                            let minimum_percent = 1+(recommended_value/100);
                            let sp_value = minimum_percent * minimum_selling_price;
                            row.find('.recommended_selling_price').val(accounting.formatNumber(sp_value))
                        })
                    }else if(type == 'fixed'){
                        $('#productTbl tbody tr').each(function(){
                            const row = $(this);
                            const minimum_selling_price = accounting.unformat(row.find('.minimum_selling_price').val() || 0);
                            let sp_val = recommended_value + minimum_selling_price;
                            row.find('.recommended_selling_price').val(accounting.formatNumber(sp_val))
                        })
                    }
                }else{
                    $('#recommend_type').val(Index.previousRecommendedType);
                    $('#recommended_value').val(Index.previousRecommendedPercentFixed);
                }
                
            },
            typeChange(){
                if (confirm('All Minimum Selling Prices will be recalculated. Do you want to proceed?')) {
                    const type = $('#type').val();
                    const percent_fixed_value = accounting.unformat($('#percent_fixed_value').val());
                    let value = 0
                    if(type == 'percentage'){
                        $('#productTbl tbody tr').each(function(){
                            const row = $(this);
                            const landing_price = accounting.unformat(row.find('.landed_price').val() || 0);
                            let minimum_percent = 1+(percent_fixed_value/100);
                            let sp_value = minimum_percent * landing_price;
                            row.find('.minimum_selling_price').val(accounting.formatNumber(sp_value))
                        })
                    }else if(type == 'fixed'){
                        $('#productTbl tbody tr').each(function(){
                            const row = $(this);
                            const landing_price = accounting.unformat(row.find('.landed_price').val() || 0);
                            let sp_val = percent_fixed_value + landing_price;
                            row.find('.minimum_selling_price').val(accounting.formatNumber(sp_val))
                        })
                    }
                } else {
                    $('#type').val(Index.previousType);
                    $('#percent_fixed_value').val(Index.previousPercentFixed);
                }
                
            },
            importChange(){
                const import_request_id = $(this).val();
                $('#productTbl tbody').html('');
                $.ajax({
                    url: "{{route('biller.import_requests.get_products')}}",
                    method: "POST",
                    data: {
                        import_request_id: import_request_id
                    },
                    success: function(data){
                        console.log(data)
                        data.forEach((v,i) => {
                            $('#productTbl tbody').append(Index.productRow(v,i));
                        });

                    }
                });
            },
            productRow(v,i){
                return `
                    <tr>
                        <td>${i+1}</td>
                        <td>${v.product_name}</td>
                        <td>${+v.qty}</td>
                        <td>${v.unit}</td>
                        <td><input type="text" name="landed_price[]" class="form-control landed_price" value="${v.avg_rate_shippment_per_item}" id="landed_price-${i+1}" readonly></td>
                        <td><input type="text" name="minimum_selling_price[]" class="form-control minimum_selling_price" id="minimum_selling_price-${i+1}"></td>
                        <td><input type="text" name="recommended_selling_price[]" class="form-control recommended_selling_price" id="recommended_selling_price-${i+1}"></td>
                        <td><input type="text" name="moq[]" class="form-control moq"  id="moq-${i+1}"></td>
                        <td><input type="text" name="reorder_level[]" class="form-control reorder_level" id="reorder_level-${i+1}"></td>
                        <input type="hidden" name="product_id[]" value="${v.product_id}">
                        <input type="hidden" name="import_request_item_id[]" value="${v.id}">
                    </tr>
                `;
            }
        };
        $(()=> Index.init());
    </script>
@endsection