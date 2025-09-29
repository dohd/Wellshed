@extends ('core.layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Attendance Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.attendances.partials.attendances-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">
                    <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#leaveStatusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $employee_name = '';
                            $phone_number = '';
                            $employee = $attendance->employee;
                            if ($employee) {
                                $employee_name = $employee->first_name . ' ' . $employee->last_name;
                                $phone_number = $employee->meta ? $employee->meta->primary_contact : '';
                            }
                        
                            $details = [
                                'Date' => dateFormat($attendance->date),
                                'Employee' => $employee_name,
                                'Phone Number' => $phone_number,
                                'Clock In' => $attendance->clock_in,
                                'Clock Out' => $attendance->clock_out,
                                'Hours' => +$attendance->hrs,
                                'Attendance Status' => $attendance->status,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th>{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.attendances.partials.leave-status-modal')
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
    };
    const Form = {
        init(){
            $.ajaxSetup(config.ajax);
            $('#type').change(Form.typeChange);
            $('#status').change(Form.statusChange);
        },
        typeChange(){
            const type = $(this).val();
            if(type == 'clock_in'){
                $('#in').removeClass('d-none');
                $('#out').addClass('d-none');
            }else if(type == 'clock_out'){
                $('#out').removeClass('d-none');
                $('#in').addClass('d-none');
            }
        },
        statusChange() {
            const status = $(this).val();
            // $('.status_note').attr('readonly',true);
            if (status == 'on_leave') {
                $('.clock-in').val('');
                $('.clock-out').val('');
            }else if(status == 'absent'){
                $('.clock-in').val('');
                $('.clock-out').val('');
                $('.status_note').attr('readonly',false);
            }else if(status == 'late'){
                $('.status_note').attr('readonly',false);
            }
        },
    };
    $(() => Form.init())
</script>
@endsection