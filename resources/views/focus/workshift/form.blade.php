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
                @php
                    $days = ['Monday', 'Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                @endphp
                <tr>
                        @foreach ($days as $k => $day)
                            <tr>
                                <td><input type="text" class="form-control day col round" value="{{$day}}" name="weekday[]" placeholder="eg. Monday" id="day-{{$k}}">
                                </td>
                                
                                <td><input type="time" class="form-control clock_in" value="08:00" name="clock_in[]" id="clock_in-{{$k}}"></td>
                                <td>
                                    <input type="hidden" class="form-control hour" value="0" name="hours[]" disabled>
                                    <select name="hours[]" id="hours-{{$k}}" class="form-control hours">
                                        <option value="0.5">30 min</option>
                                        <option value="1">1 hour</option>
                                        <option value="1.5">1 hour 30 min</option>
                                        <option value="2">2 hours</option>
                                        <option value="2.5">2 hours 30 min</option>
                                    </select>
                                </td>
                                <td><input type="time" class="form-control clock_out" value="17:00" name="clock_out[]" id="clock_out-{{$k}}"></td>
                                <td><input type="checkbox" class="form-control remove" value="0" name="is_checked[]" id="remove-{{$k}}">
                                </td>
                                <input type="hidden" class="status" name="status[]" value="0" id="status-{{$k}}">
                                
                            </tr>
                        @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>