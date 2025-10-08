<div class='form-group row'>
    <div class='col-lg-4'>
        {{ Form::label('name', 'Name', ['class' => 'control-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Name']) }}
    </div>
    <div class='col-lg-8'>
        {{ Form::label('description', 'Description', ['class' => 'control-label']) }}
        {{ Form::text('description', null, ['class' => 'form-control ', 'placeholder' => 'Description']) }}
    </div>
</div>
<div class='form-group row'>
    <div class="col-6">
        <div class="table-responsive">
            <table id="daysTbl" class="table" widht="50%">
                <thead>
                    <tr>
                        <th>Sub Zone Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                           <input type="text" name="sub_zone_name[]" id="sub_zone_name-0" class="form-control">
                        </td>
                       
                        <td>
                            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove">
                                <i class="fa fa-trash fa-lg text-danger"></i>
                            </button>
                        </td>
                    </tr>
                    @isset($customer_order)
                        @if (count($customer_order->deliver_days) > 0)
                            @foreach ($customer_order->deliver_days as $item)
                                <tr>
                                    @php
                                        $days = [
                                            'Monday',
                                            'Tuesday',
                                            'Wednesday',
                                            'Thursday',
                                            'Friday',
                                            'Sarturday',
                                            'Sunday',
                                        ];
                                    @endphp
                                    <td>
                                        <select name="delivery_days[]" id="delivery_days" class="form-control">
                                            <option value="">Select a day</option>
                                            @foreach ($days as $day)
                                                <option value="{{ $day }}"
                                                    {{ $item->delivery_days == $day ? 'selected' : '' }}>{{ $day }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        {{ Form::time('expected_time[]', $item->expected_time, ['class' => 'form-control', 'placeholder' => 'Expected Delivery Time']) }}
                                    </td>
                                    <input type="hidden" name="d_id[]" value="{{ $item->id }}" id="">
                                    <td>
                                        <button type="button" class="btn btn-outline-light btn-sm mt-1 remove">
                                            <i class="fa fa-trash fa-lg text-danger"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endisset
                </tbody>
            </table>
        </div>
        <button class="btn btn-success btn-sm ml-2" type="button" id="addDoc">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
        </button>
    </div>
</div>
