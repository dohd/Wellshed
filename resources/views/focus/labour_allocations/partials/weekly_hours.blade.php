<fieldset class="border p-1 mb-3 weekly-hrs-ctn d-none">
    <legend class="w-auto float-none h5">Weekly Casuals Hours</legend>
    <p class="alert alert-light mb-1">
        Imported data format should be as per downloaded template file. 
        <a href="{{ route('biller.import.sample_template', 'labour_hours') }}" target="_blank" id="download-btn">
            <b>{{ trans('import.download_template') }}</b> (*.csv)
        </a>
    </p>
    <div class="form-row mb-2">
        {!! Form::file(null, array('class'=>'form-control input col-md-3', 'id' => 'csvUpload' )) !!}
    </div>
    <div class="form-row mb-1">
        <div class="col-2" style="max-width: 120px;">Date Between</div>
        <div class="col-2" style="max-width: 120px;">
            {{ Form::text('period_from', null, ['id' => 'period_from', 'placeholder' => date('d-m-Y'), 'class' => 'form-control form-control-sm datepicker']) }}
        </div>
        <div class="col-2" style="max-width: 120px;">
            {{ Form::text('period_to', null, ['id' => 'period_to', 'placeholder' => date('d-m-Y'), 'class' => 'form-control form-control-sm datepicker']) }}
        </div>
    </div>
    <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
        <table id="employeesTbl" class="table tfr my_stripe_single pb-2 text-center">
            <thead>
                <tr>
                    <th colspan="4">Employee</th>
                    <th colspan="7">Regular Hours</th>
                    <th colspan="8">Overtime Hours</th>
                </tr>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th></th>
                    <th>#</th>
                    <th style="min-width: 150px;">ID</th>
                    <th style="min-width: 200px;">Full Name</th>
                    <th style="border-left: 3px solid white;">Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>   
                    <th >Sun</th>
                    <th style="border-left: 3px solid white;">Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Sat</th>  
                    <th>Sun</th> 
                    <th style="border-left: 3px solid white;">Total</th>                
                </tr>
            </thead>
            <tbody>
                @if(@$labour_allocation && $labour_allocation->casualWeeklyHrs->count())
                    @foreach ($labour_allocation->casualLabourers as $i => $row)
                        <tr>
                            <td><i class="fa fa-trash fa-lg text-danger del-row" style="cursor: pointer;"></i></td>
                            <td class="index">{{ $i+1 }}</td>
                            <td class="empl-id">{{ $row->id_number }}</td>
                            <td>
                                <select name="casual_labourer_id[]" class="custom-select casual-labourer" data-placeholder="Search Name">
                                    <option value=""></option>
                                    <option value="{{ $row->id }}" selected>{{ $row->name }}</option>
                                </select>
                            </td>
                            @php 
                                $regHrs = $row->casualWeeklyHrs->whereNull('is_overtime')->first();
                                $otHrs = $row->casualWeeklyHrs->whereNotNull('is_overtime')->first(); 
                            @endphp
                            <!-- regular -->
                            <td><input type="text" name="regular_hrs[]" value="{{ +@$regHrs->mon }}" class="form-control form-control-sm reg-hrs"></td>
                            <td><input type="text" name="regular_hrs[]" value="{{ +@$regHrs->tue }}" class="form-control form-control-sm reg-hrs"></td>
                            <td><input type="text" name="regular_hrs[]" value="{{ +@$regHrs->wed }}" class="form-control form-control-sm reg-hrs"></td>
                            <td><input type="text" name="regular_hrs[]" value="{{ +@$regHrs->thu }}" class="form-control form-control-sm reg-hrs"></td>
                            <td><input type="text" name="regular_hrs[]" value="{{ +@$regHrs->fri }}" class="form-control form-control-sm reg-hrs"></td>
                            <td><input type="text" name="regular_hrs[]" value="{{ +@$regHrs->sat }}" class="form-control form-control-sm reg-hrs"></td>
                            <td><input type="text" name="regular_hrs[]" value="{{ +@$regHrs->sun }}" class="form-control form-control-sm reg-hrs"></td>
                            <!-- overtime -->
                            <td><input type="text" name="overtime_hrs[]" value="{{ +@$otHrs->mon }}" class="form-control form-control-sm ot-hrs"></td>
                            <td><input type="text" name="overtime_hrs[]" value="{{ +@$otHrs->tue }}" class="form-control form-control-sm ot-hrs"></td>
                            <td><input type="text" name="overtime_hrs[]" value="{{ +@$otHrs->wed }}" class="form-control form-control-sm ot-hrs"></td>
                            <td><input type="text" name="overtime_hrs[]" value="{{ +@$otHrs->thu }}" class="form-control form-control-sm ot-hrs"></td>
                            <td><input type="text" name="overtime_hrs[]" value="{{ +@$otHrs->fri }}" class="form-control form-control-sm ot-hrs"></td>
                            <td><input type="text" name="overtime_hrs[]" value="{{ +@$otHrs->sat }}" class="form-control form-control-sm ot-hrs"></td>
                            <td><input type="text" name="overtime_hrs[]" value="{{ +@$otHrs->sun }}" class="form-control form-control-sm ot-hrs"></td>
                            <td>
                                <input type="text" name="total_hrs[]" class="form-control form-control-sm total-hrs" readonly>
                                <input type="hidden" name="total_reg_hrs[]" class="reg-total">
                                <input type="hidden" name="total_ot_hrs[]" class="ot-total">
                            </td>
                        </tr>
                    @endforeach
                @endif

                <!-- template row -->
                <tr>
                    <td><i class="fa fa-trash fa-lg text-danger del-row" style="cursor: pointer;"></i></td>
                    <td class="index">1</td>
                    <td class="empl-id"></td>
                    <td>
                        <select name="casual_labourer_id[]" class="custom-select casual-labourer" data-placeholder="Search Name">
                            <option value=""></option>
                        </select>
                    </td>
                    <!-- regular -->
                    @foreach (range(1,7) as $value)
                    <td><input type="text" name="regular_hrs[]" class="form-control form-control-sm reg-hrs"></td>
                    @endforeach
                    <!-- overtime -->
                    @foreach (range(1,7) as $value)
                        <td><input type="text" name="overtime_hrs[]" class="form-control form-control-sm ot-hrs"></td>
                    @endforeach
                    <td>
                        <input type="text" name="total_hrs[]" class="form-control form-control-sm total-hrs" readonly>
                        <input type="hidden" name="total_reg_hrs[]" class="reg-total">
                        <input type="hidden" name="total_ot_hrs[]" class="ot-total">
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-success btn-sm ml-1" id="addRowBtn">
            <i class="fa fa-plus-square"></i> Add Row
        </button>
    </div>
</fieldset>