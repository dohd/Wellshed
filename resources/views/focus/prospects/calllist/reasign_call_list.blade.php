@extends ('core.layouts.app')

@section ('title', 'Call List Reassignment | Create')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Call List Reassignment</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-auto float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.prospects.partials.prospects-header-buttons')
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="d-flex  flex-row ">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.calllists.store_reassign', 'method' => 'POST' ]) }}
                                @php
                                $date = date("Y-m-d");
                                @endphp
                                <div class="form-group row">
                                    <div class="col-4">
                                        <label for="">Choose Title</label>
                                        <select id="title" name="title" class="form-control" data-placeholder="Choose Title" required>
                                            <option value="">Choose Title</option>
                                            @foreach ($excel as $row)
                                                <option value="{{ $row->title }} -({{ $date }})" count="{{ $row->count }}">
                                                    {{ $row->title }} - Total {{ $row->count }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="category" value="excel" id="">
                                    <div class="col-4">
                                        <label for="">Assign From User<span
                                            class="text-danger">*</span></label>
                                        <select name="employee_from_id" id="employee_from_id" class="form-control" data-placeholder="Choose Employee" required>
                                            <option value="">Choose Employee</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{$employee->id}}">{{$employee->fullname}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <label for="">Total No of Prospects</label>
                                        <input type="text" name="total_prospects" id="total_prospect" class="form-control" readonly>
                                    </div>
                                    <div class="col-2">
                                        <label for="">No of Prospects To Assign<span
                                            class="text-danger">*</span></label>
                                        <input type="text" name="prospect_to_assign" id="prospect_to_assign" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-4"><label for="start_date" class="caption">Start date<span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'Start', 'id' => 'start_date', 'required']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-4"><label for="end_date" class="caption">End date<span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                                            {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'End', 'id' => 'end_date', 'required']) }}
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <label for="">User To Assign <span
                                            class="text-danger">*</span></label>
                                        <select name="employee_id" id="employee" class="form-control" data-placeholder="Choose Employee" required>
                                            <option value="">Choose Employee</option>
                                            @foreach ($employees as $employee)
                                                <option value="{{$employee->id}}">{{$employee->fullname}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="column">
                                    {{ link_to_route('biller.prospects.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md col-1 ml-auto mr-1']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md col-1']) }}                                           
                                </div>
                            {{ Form::close() }}
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
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Index = {
            init(){
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $.ajaxSetup(config.ajax);
                $('#employee_from_id').select2({ allowClear: true});
                $('#employee').select2({ allowClear: true});
                $('#title').select2({ allowClear: true});
                $('#title').change(this.callListChange);
                $('#employee_from_id').change(this.employeeFromChange);
                $('#prospect_to_assign').change(this.prospectToAssignChange);
            },

            prospectToAssignChange(){
                let count = accounting.unformat($('#prospect_to_assign').val());
                let total_prospect =  accounting.unformat($('#total_prospect').val());
                console.log(count, total_prospect);
                if(count > total_prospect){
                    $('#prospect_to_assign').val(total_prospect);
                }
            },

            employeeFromChange(){
                const employee_from_id = $('#employee_from_id').val();
                let title = $('#title option:selected').val();
                $.ajax({
                    url: "{{route('biller.calllists.get_user_call_lists')}}",
                    method: "POST",
                    data: {
                        title: title,
                        employee_from_id: employee_from_id,
                    },
                    success: function(data) {
                        console.log(data);
                        $('#total_prospect').val(data);
                    }
                });
            },
            callListChange(){
                let count = $('#title option:selected').attr('count');
                
                $('#total_prospect').val(count);
                $('#employee_from_id').change();
                $('#prospect_to_assign').val('');
            }
        };
        $(() => Index.init())
    </script>
@endsection