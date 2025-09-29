<div class="card">
   <div class="card-content">
    <div class="card-body">
        <div class='form-group'>
            {{ Form::label( 'name','WorkShift Name',['class' => 'col-lg-2 control-label']) }}
            <div class='col-lg-10'>
                {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' =>'WorkShift Name','id'=>'name']) }}
            </div>
        </div>
        
    </div>
    <div class="table-responsive">        
        <table id="itemTbl" class="table">
            <thead>
                <tr class="bg-gradient-directional-blue white round">
                    <th width="40%">Day of Week</th>
                    <th>Clock In</th>
                    <th>Hours</th>
                    <th>Clock Out</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @isset ($workshift_items)
                    @php ($i = 0)
                    @foreach ($workshift_items as $item)
                        @if ($item)
                            <tr>
                                <td>
                                    <input type="text" class="form-control day col round" value="{{$item->weekday}}" name="weekday[]" placeholder="eg. Monday" id="day-0">
                                    
                                </td>
                                <td><input type="time" class="form-control clock_in" name="clock_in[]" value="{{$item->clock_in}}" id="clock_in-{{$i}}}"></td>
                                <td>
                                    <input type="hidden" class="form-control hour" value="0" name="hours[]" disabled>
                                    <select name="hours[]" id="hours-{{$i}}" class="form-control hours">
                                        <option value="0.5" {{$item->hours == 0.5 ? 'selected' : ''}}>30 min</option>
                                        <option value="1" {{$item->hours == 1 ? 'selected' : ''}}>1 hour</option>
                                        <option value="1.5" {{$item->hours == 1.5 ? 'selected' : ''}}>1 hour 30 min</option>
                                        <option value="2" {{$item->hours == 2 ? 'selected' : ''}}>2 hours</option>
                                        <option value="2.5" {{$item->hours == 2.5 ? 'selected' : ''}}>2 hours 30 min</option>
                                    </select>
                                </td>
                                <td><input type="time" class="form-control clock_out" name="clock_out[]" value="{{$item->clock_out}}" id="clock_out-{{$i}}"></td>
                                <td><input type="checkbox" class="form-control remove" value="1" name="is_checked[]" id="" @isset($item)
                                    {{($item->is_checked == 1 ? 'checked': '')}}
                                @endisset></td>
                                <input type="hidden" class="id" name="id[]" value="{{$item->id}}" id="id-{{$i}}">
                                <input type="hidden" class="status" name="status[]" value="{{$item->is_checked}}" id="id-{{$i}}">
                                
                            </tr>
                            @php ($i++)
                        @endif
                    @endforeach
                @endisset
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>