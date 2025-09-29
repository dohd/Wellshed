{{-- @include('tinymce.scripts') --}}


<div class="row mb-2">

    <div class="col-10 col-lg-7">
        <label for="department">Department:</label>
        <select class="form-control box-size" id="department" name="department">
            <option value="">-- Select Department --</option>
            @foreach ($departments as $val)
                <option value="{{ $val }}">
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
                <option value="{{$key_activity->id}}">{{$key_activity->name}}</option>
            @endforeach
        </select>
        {{-- <textarea name="key_activities" id="" cols="30" rows="1" class="form-control box-size"></textarea> --}}
    </div>

    <div class="col-10 col-lg-7">
        <label for="name" class="mt-2">Task Name/Key Performance Indicator:</label>
        <input type="text" id="name" name="name" required class="form-control box-size mb-2">
    </div>

    <div class="col-10 col-lg-7 row mb-2">
        <div class="col-4">
            <label for="">Target</label>
            <input type="text" name="target" id="target" class="form-control">
        </div>
        <div class="col-3">
            <label for="">Unit of Measure</label>
            <select name="uom" id="uom" class="form-control">
                <option value="">--select uom</option>
                @foreach (['%','KSH','USD','TSH','UGX','PC','LOT','ITEM'] as $item)
                    <option value="{{$item}}">{{$item}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-10 col-lg-7">
        <label for="frequency">Frequency:</label>
        <select class="form-control box-size" id="frequency" name="frequency">
            <option value="">-- Select Frequency --</option>
            @foreach ($frequency as $val)
                <option value="{{ $val }}">
                    {{ $val }}
                </option>
            @endforeach
        </select>
    </div>

</div>


