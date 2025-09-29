<div class="form-group row">
    <div class="col-4">
        <label for="title">Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'id' => 'title', 'required']) }}
    </div>
    <div class="col-8">
        <label for="note">Note</label>
        {{ Form::text('note', null, ['class' => 'form-control', 'id' => 'note', 'required']) }}
    </div>
</div>

<div class="form-group row">
    <div class="col-2">
        <label for="recurrent">Is Recurrent Holiday</label>
        <select name="is_recurrent" id="is_recurrent" class="custom-select">
            @foreach ([1 => 'yes', 0 => 'no'] as $k => $val)
                <option value="{{ $k }}" {{ @$holiday_list && $holiday_list->is_recurrent == $k? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
    </div>
</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.holiday_list.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$holiday_list? 'Update' : 'Create', ['class' => 'form-control btn btn-primary']) }}
    </div>
</div>

@section('extra-scripts')
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        holiday: @json(@$holiday_list),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());

            if (this.holiday) {
                $('.datepicker').datepicker('setDate', new Date(this.holiday.date));
            }
        },

    };

    $(() => Index.init());
</script>
@endsection