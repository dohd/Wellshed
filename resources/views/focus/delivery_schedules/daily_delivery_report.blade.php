@extends ('core.layouts.app')

@section ('title', 'Daily Delivery Report')

@section('page-header')
    <h1>
        <small>Daily Delivery Report</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Daily Delivery Report</h4>

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
                                    {{ Form::open(['route' => 'biller.delivery_schedules.exportPdf', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'target' => '_blank']) }}


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
    <script>
       const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
       }; 
       const Index = {
            init(){
                $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            },
       };
       $(()=>Index.init());
    </script>
@endsection