<div class="col-sm-12  cmp-pnl">
    <div id="customerpanel" class="inner-cmp-pnl">
        <div class="form-group row">
            <div class="fcol-sm-12">
                <h3 class="title pl-1">Call Allocation</h3>
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
                            <option value="{{ $row->title }} -({{ $date }})" count="{{ $row->count }}" {{$row->title == $calllist->title ? 'selected' : ''}}>
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
                    {{ Form::text('prospects_number',  null , ['class' => 'form-control', 'placeholder' => 'Prospects to be added in range', 'id' => 'prospects_number', 'required','readonly']) }}
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
                        <option value="{{$employee->id}}" {{$employee->id == $calllist->employee_id ? 'selected' : ''}}>{{$employee->fullname}}</option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>
</div>

@section('after-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
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

        const Form = {
            
            direct: @json(@$direct),
            excel: @json(@$excel),
            calllist: @json(@$calllist),
            init() {
                $('#start_date').datepicker(config.date).datepicker('setDate', new Date(this.calllist.start_date));
                $('#end_date').datepicker(config.date).datepicker('setDate', new Date(this.calllist.end_date));
                // $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#employee').select2({
                    allowClear: true
                });
               
               
            },

        };

        $(() => Form.init());
    </script>
@endsection

