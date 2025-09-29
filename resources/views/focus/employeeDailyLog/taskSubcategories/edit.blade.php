@extends ('core.layouts.app')

@section ('title', 'Edit EDL Key Activity')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Edit EDL Key Activity</h3>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::model($taskSubcategory, ['route' => ['biller.employee-task-subcategories.update', $taskSubcategory->id], 'method' => 'PATCH']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}

                                <div class="row mb-2">

                                    <div class="col-10 col-lg-7">
                                        <label for="department">Department:</label>
                                        <select class="form-control box-size" id="department" name="department">
                                            <option value="">-- Select Department --</option>
                                            @foreach ($departments as $val)
                                                <option value="{{ $val }}" @if ($val == $taskSubcategory->department) selected @endif>
                                                    {{ $val }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-10 col-lg-7">
                                        <label for="key_activities" class="mt-2">Key Activity</label>
                                        <select name="key_activity_id" id="key_activity_id" class="form-control" data-placeholder="Search Key Activity">
                                            <option value="">Search Key Activity</option>
                                            @foreach ($key_activites as $key_activity)
                                                <option value="{{$key_activity->id}}" {{$taskSubcategory->key_activity_id == $key_activity->id ? 'selected' : ''}}>{{$key_activity->name}}</option>
                                            @endforeach
                                        </select>
                                        {{-- <textarea name="key_activities" id="" cols="30" rows="1" class="form-control box-size">{{$taskSubcategory->key_activities}}</textarea> --}}
                                    </div>

                                    <div class="col-10 col-lg-7">
                                        <label for="name" class="mt-2">Task Name/Key Performance Indicator:</label>
                                        <input type="text" id="name" name="name" required class="form-control box-size mb-2" value="{{ $taskSubcategory->name }}">
                                    </div>
                                    <div class="col-10 col-lg-7 row mb-2">
                                        <div class="col-4">
                                            <label for="">Target</label>
                                            <input type="text" name="target" id="target" value="{{$taskSubcategory->target}}" class="form-control">
                                        </div>
                                        <div class="col-3">
                                            <label for="">Unit of Measure</label>
                                            <select name="uom" id="uom" class="form-control">
                                                <option value="">--select uom</option>
                                                @foreach (['%','KSH','USD','TSH','UGX','PC','LOT','ITEM'] as $item)
                                                    <option value="{{$item}}" {{$item == $taskSubcategory->uom ? 'selected' : ''}}>{{$item}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-10 col-lg-7">
                                        <label for="frequency">Frequency:</label>
                                        <select class="form-control box-size" id="frequency" name="frequency">
                                            <option value="">-- Select Frequency --</option>
                                            @foreach ($frequency as $val)
                                                <option value="{{ $val }}"  @if ($val == $taskSubcategory->frequency) selected @endif>
                                                    {{ $val }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.employee-task-subcategories.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                    {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}
                                    <div class="clearfix"></div>
                                </div>
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

<style>
    .radius-8 {
        border-radius: 8px;
    }
</style>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $('#key_activity_id').select2({allowClear: true});
</script>
@endsection