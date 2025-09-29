<div class="col-sm-12  cmp-pnl">
    <div id="customerpanel" class="inner-cmp-pnl">
        <div class="form-group row">
            <div class="fcol-sm-12">
                <h3 class="title pl-1">Call Allocation</h3>
            </div>
        </div>
        <div class="form-group row mb-2">
            <div class='col-sm-6 '>

                <div><label for="prospect-type">Select Prospect Type</label></div>
                <div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-primary prospect-type" name="category"
                        id="colorCheck1" value="direct" checked>
                    <label class="custom-control-label" for="colorCheck1">Direct</label>
                </div>
                <div class="d-inline-block custom-control custom-checkbox mr-1">
                    <input type="radio" class="custom-control-input bg-purple prospect-type" name="category"
                        value="excel" id="colorCheck3">
                    <label class="custom-control-label" for="colorCheck3">Excel Upload</label>
                </div>

            </div>

        </div>
        <div class="form-group row">
            <div class="col-sm-4 direct-prospects" hidden>
                <div class=" h6">Direct Prospects Count</div>
                <div id="directprospectcount" class=" h6 prospect-count">0</div>
            </div>
        </div>
        @php
        $date = date("Y-m-d");
        @endphp
        <div class="form-group row">
            <div class="col-sm-4"><label for="group_title" class="caption">Group Title</label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                    <select id="title" name="title" class="form-control" data-placeholder="Choose Title" disabled
                        required>
                        <option value="">Choose Title</option>
                        @foreach ($excel as $row)
                            <option value="{{ $row->title }} -({{ $date }})" count="{{ $row->count }}">
                                {{ $row->title }} - Total {{ $row->count }}
                            </option>
                        @endforeach

                    </select>
                </div>
            </div>
            <div class="col-sm-4">
                <label for="prospects_number" class="caption">Number of Prospects<span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
                    {{ Form::text('prospects_number',  null , ['class' => 'form-control', 'placeholder' => 'Prospects to be added in range', 'id' => 'prospects_number', 'required']) }}
                </div>
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
        </div>

        <div class="form-group row">
            <div class="col-4">
                <label for="">Assign User</label>
                <select name="employee_id" id="employee" class="form-control" data-placeholder="Choose Employee">
                    <option value="">Choose Employee</option>
                    @foreach ($employees as $employee)
                        <option value="{{$employee->id}}">{{$employee->fullname}}</option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>
</div>




@section('after-scripts')
    @include('focus.prospects.calllist.form_js')
@endsection
