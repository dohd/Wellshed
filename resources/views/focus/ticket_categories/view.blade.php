@extends ('core.layouts.app')

@section('title', 'Leave Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Leave Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.leave.partials.leave-header-buttons')
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
                            $employee = $leave->employee;
                            if ($employee) $employee_name = $employee->first_name . ' ' . $employee->last_name;
                        
                            $details = [
                                'Employee' => $employee_name,
                                'Leave Category' => $leave->leave_category? $leave->leave_category->title : '',
                                'Leave Status' => $leave->status,
                                'Leave Reason' => $leave->reason,
                                'Leave Duration' => $leave->qty . ' days',
                                'Start Date' => dateFormat($leave->start_date),
                                'End Date' => dateFormat($leave->end_date),
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Leave Status')
                                        <span class="text-success">{{ $val }}</span>
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.leave.partials.leave-status-modal')
@endsection
