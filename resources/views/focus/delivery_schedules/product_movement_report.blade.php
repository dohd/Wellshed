@extends ('core.layouts.app')

@section ('title', 'Product Movement Report')

@section('page-header')
    <h1>
        <small>Product Movement Report</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Product Movement Report</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            {{-- @include('focus.delivery_schedules.partials.delivery_schedules-header-buttons') --}}
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
                                    {{ Form::open(['route' => 'biller.delivery_schedules.product_movement_pdf', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'target' => '_blank']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="start_date">Start Date</label>
                                                <input type="text" name="start_date" id="start_date" class="form-control datepicker">
                                            </div>
                                            
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="end_date">End Date</label>
                                                <input type="text" name="end_date" id="end_date" class="form-control datepicker">
                                            </div>
                                            
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="product">Search Product</label>
                                                <select name="product_id" id="product" class="form-control" data-placeholder="Search Product">
                                                    <option value="">Search Product</option>
                                                    @foreach ($products as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-lg-5">
                                                <label for="start_date">OutPut</label>
                                                <select name="output" id="output" class="form-control">
                                                    <option value="pdf">PDF</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="edit-form-btn">
                                           
                                            {{ Form::submit('Generate', ['class' => 'btn btn-primary btn-md']) }}
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
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
       }; 
       const Index = {
            init(){
                $.ajaxSetup(config.ajax);
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#product').select2({allowClear:true})
            },
       };
       $(()=>Index.init());
    </script>
@endsection