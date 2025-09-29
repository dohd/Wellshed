<div class="row p-1">
    @if(access()->allow('hrm'))
        @foreach($permissions_all as $row)
            <div class="col-md-6">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="permission[]" value="{{$row['id']}}" class="permission">
                    <label> {{ $row['display_name'] }}</label>
                </div>
            </div>
        @endforeach
    @endif
</div>