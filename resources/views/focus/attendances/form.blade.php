@php
    $currentMonthName = now()->format('F');  // Returns the month name (e.g., 'October')
    $currentYear = now()->format('Y'); 
    $date = $currentMonthName .' ' . $currentYear;
    $now = now()->format('m'); // Returns
@endphp
<h5><b>{{$date}}</b></h5>
<div class="form-group row">
    <div class="col-2">
        <label for="month">Monthly Calendar Days</label>
        <select name="month" id="month" class="custom-select">
            @foreach (range(1,12) as $v)
                <option value="{{ $v }}" {{$now == $v ? 'selected' : ''}}>
                    {{ DateTime::createFromFormat('!m', $v)->format('F') }}
                </option>
            @endforeach
        </select>
        {{ Form::text('day', null, ['class' => 'form-control mt-1', 'placeholder' => 'attendance day', 'id' => 'day', 'required','readonly']) }}
    </div>
    <div class="col-10">
        <h3 class="calendar-title text-center font-weight-bold"></h3>
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <div class="table-responsive">
            <table id="weeksTbl" class="table table-bordered text-center">
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <div class="table-responsive">
            <table id="employeeTbl" class="table tfr my_stripe_single text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th width="50%">Employee Name</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $i => $row)
                        <tr class="hidden">
                            <td class="index">{{ $i+1 }}</td>
                            <td class="employee-name">{{ $row->first_name }} {{ $row->last_name }}</td>
                            <td><input type="time" name="clock_in[]" value="{{ $company->clock_in }}" placeholder="HH:MM" class="form-control clock-in"></td>
                            <td><input type="time" name="clock_out[]" value="{{ $company->clock_out }}" placeholder="HH:MM" class="form-control clock-out"></td>
                            <td>
                                <select name="status[]" class="custom-select status">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="late">Late/Away</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="status_note[]" id="status_note" class="form-control status_note" readonly>
                            </td>
                            {{ Form::hidden('employee_id[]', $row->id, ['class' => 'employee-id']) }}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.attendances.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$attendance? 'Update' : 'Generate', ['class' => 'form-control btn btn-primary text-white hidden']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        defaultEmployeeRows: '',

        init() {
            $.ajaxSetup(config.ajax);
            Index.defaultEmployeeRows = $('#employeeTbl tbody').html().replace(/class="hidden"/g, '');

            $('#weeksTbl').on('click', '.day-btn', this.dayBtnClick);
            $('#employeeTbl').on('change', '.status', this.statusChange);

            $('#month').change(this.monthChange).trigger('change');
            $('#day').focus(() => {alert('Please, click on day from calendar!')});
        },

        statusChange() {
            const row = $(this).parents('tr');
            const status = $(this).val();
            row.find('.status_note').attr('readonly',true);
            if (status == 'on_leave') {
                row.find('.clock-in').val('');
                row.find('.clock-out').val('');
            }else if(status == 'absent'){
                row.find('.clock-in').val('');
                row.find('.clock-out').val('');
                row.find('.status_note').attr('readonly',false);
            }else if(status == 'late'){
                row.find('.status_note').attr('readonly',false);
            }
        },

        dayBtnClick() {
            const day = $(this).text();
            const monthLabel = $('#month option:selected').text().replace(/\s+/g, '');
            $('.calendar-title').text(`Attendance for ${monthLabel}, day ${day}`);
            $('#day').val(day);
            $('input:submit').removeClass('hidden');
            Index.loadAttendanceEmployees();
        },

        monthChange() {
            $('.calendar-title').text('');
            $('#day').val('');
            $('input:submit').addClass('hidden');

            const monthIndx = $(this).val();
            const year = new Date().getFullYear();
            const daysInMonth = new Date(year, monthIndx, 0).getDate();
            const daysRange = [...Array(daysInMonth).keys()].map(v => v + 1);

            const weeks = daysRange.reduce((init, curr) => {
                const i = init.length - 1;
                if (curr % 7 == 0) {
                    init[i].push(curr);
                    init.push([]);
                } else init[i].push(curr);
                return init;
            }, [[]]);

            const rows = Index.loadWeekRow(weeks);
            $('#weeksTbl tbody').html('').append(rows);
            Index.attendanceCount();
        },  

        attendanceCount() {
            const month =  $('#month').val();
            const url = "{{ route('biller.attendances.day_attendance') }}";
            $.post(url, {month}, data => {
                const dayAttendance = data.day_attendance;
                const employeeCount = data.employee_count;

                $('#weeksTbl').find('td').each(function () {
                    const td = $(this);
                    let count = 0;
                    const monthDay = td.find('.day-btn').text();
                    dayAttendance.forEach(v => {
                        if (v.day == monthDay) count = v.count;
                    });
                    if (count) td.find('.attendance-ratio').text(`${count}/${employeeCount}`);
                    // disable future dates
                    const today = new Date().getDate()
                    const thisMonth = new Date().getMonth() + 1;
                    if (month > thisMonth) {
                        td.find('.day-btn').prop('disabled', true);
                        td.addClass('bg-light')
                    } else if (month == thisMonth && monthDay > today) {
                        td.find('.day-btn').prop('disabled', true);
                        td.addClass('bg-light')
                    }  else {
                        td.find('.day-btn').prop('disabled', false);
                        td.removeClass('bg-light')
                    }
                });
            });
        },

        loadWeekRow(weeks = []) {
            const trList = [];
            weeks.forEach(week => {
                const tdList = [];
                week.forEach(day => {
                    const td = `
                        <td>
                            <span class="day-btn btn btn-primary round">${day}</span>
                            <sub class="attendance-ratio text-success pl-1"></sub>
                        </td>
                    `;
                    tdList.push(td);
                });
                trList.push(`<tr>${tdList.join('')}</tr>`)
            });
            return trList.join('');
        },

        loadAttendanceEmployees() {
            const day = $('#day').val();
            const month = $('#month').val();
            $('#employeeTbl tbody').html('').append(Index.defaultEmployeeRows);

            const url = "{{ route('biller.attendances.employees_attendance') }}";
            $.post(url, {day, month}, data => {
                data.forEach((v, i) => {
                    $('#employeeTbl tbody tr').each(function () {
                        const row = $(this);
                        const employeeId = row.find('.employee-id').val();
                        for (let i = 0; i < data.length; i++) {
                            const v = data[i];
                            if (employeeId == v.employee?.id) {
                                row.find('.clock-in').val(v.clock_in);
                                row.find('.clock-out').val(v.clock_out);
                                row.find('.status').val(v.status);
                                row.find('.status_note').attr('readonly',true);
                                if (v.status == 'late'){
                                    row.find('.status_note').val(v.status_note).attr('readonly', false);
                                }
                                break;
                            }
                        }
                    });
                });
            })
        },
    };

    $(() => Index.init());
</script>
@endsection