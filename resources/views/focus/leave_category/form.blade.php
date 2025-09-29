<div class="form-group row">
    <div class="col-6">
        <label for="title">Title</label>
        {{ Form::text('title', null, ['class' => 'form-control', 'id' => 'title', 'required']) }}
    </div>
    
    <div class="col-2">
        <label for="gender">Gender</label>
        <select name="gender" id="gender" class="custom-select">
            @foreach (['a' => 'all', 'm' => 'male', 'f' => 'female'] as $k => $val)
                <option value="{{ $k }}" {{ @$leave_category && $leave_category->gender == $k? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-2">
        <label for="is_payable">Payable Leave</label>
        <select name="is_payable" id="is_payable" class="custom-select">
            @foreach ([1 => 'yes', 0 => 'no'] as $k => $val)
                <option value="{{ $k }}" {{ @$leave_category && $leave_category->is_payable == $k? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-2">
        <label for="is_encashed">Encash Leave</label>
        <select name="is_encashed" id="is_encashed" class="custom-select">
            @foreach ([1 => 'no', 0 => 'yes'] as $k => $val)
                <option value="{{ $k }}" {{ @$leave_category && $leave_category->is_encashed == $k? 'selected' : '' }}>
                    {{ ucfirst($val) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-8">
        <label for="policy">Policy</label>
        {{ Form::text('policy', null, ['class' => 'form-control', 'id' => 'policy', 'required']) }}
    </div>
    <div class="col-2">
        <label for="days">Leave Duration (days)</label>
        {{ Form::number('qty', null, ['class' => 'form-control', 'min' => '1']) }}
    </div>
</div>


<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.leave_category.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$leave_category? 'Update' : 'Create', ['class' => 'form-control btn btn-primary']) }}
    </div>
</div>

@section('extra-scripts')
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        leave_category: @json(@$leave_category),

        init() {
            $.ajaxSetup(config.ajax);
        },

    };

    $(() => Index.init());
</script>
@endsection