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
                    @php
                        $approver_ids = explode(',',$leave->approver_ids);
                    @endphp
                    @if(in_array(auth()->id(), $approver_ids))
                        <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#leaveStatusModal">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Status
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $employee_name = '';
                            $employee = $leave->employee;
                            if ($employee) $employee_name = $employee->first_name . ' ' . $employee->last_name;
                            $users = [];
                            $assist_employee_id = explode(',',$leave->assist_employee_id);
                            foreach ($assist_employee_id as $employee_id) {
                                $hrm = \App\Models\hrm\Hrm::find($employee_id);
                                $users[] = $hrm->fullname;
                            }
                            $users = implode(', ', $users);
                            $approvers = [];
                            $approver_ids = explode(',',$leave->approver_ids);
                            foreach ($approver_ids as $user_id) {
                                $hrm = \App\Models\hrm\Hrm::find($user_id);
                                $approvers[] = $hrm->fullname;
                            }
                            $approvers = implode(', ', $approvers);
                            $message = "Dear " . $users. ", you have been delegated duties of " . $employee_name . " who is going on leave from " 
                            . (new DateTime($leave->start_date))->format('l, jS F Y') . " to ". (new DateTime($leave->end_date))->format('l, jS F Y').". Please ensure all tasks are covered.";
                        
                            $details = [
                                'Employee' => $employee_name,
                                'Submitted On' => (new DateTime($leave->created_at))->format('l, jS F Y'),
                                'Employee Contact Number' => @$employee->meta ? $employee->meta->primary_contact : '',
                                'From Approval' => $leave->status == 'approved' ? $text : '',
                                'Delegates' => $users,
                                'Message to Delegates' => $message,
                                'Approvers' => $approvers,
                                'Leave Category' => $leave->leave_category? $leave->leave_category->title : '',
                                // 'Leave Status' => $leave->status,
                                'Leave Reason' => $leave->reason,
                                'Leave Duration' => $leave->qty  . " " . ($leave->qty > 1 ? ' days' : ' day'),
                                'Start Date' => (new DateTime($leave->start_date))->format('l, jS F Y'),
                                'End Date' => (new DateTime($leave->end_date))->format('l, jS F Y'),
                                'Return Date' => (new DateTime($leave->return_date))->format('l, jS F Y'),
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

                <div class="card-body">
                    <legend>Approval Status</legend>
                    <table class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Status</th>
                                <th>Status Note</th>
                                <th>Approval Date</th>
                                <th>Approved By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($leave->approvers) > 0)
                                @foreach ($leave->approvers as $i => $item)
                                    <tr>
                                        <td>{{$i+1}}</td>
                                        <td>{{ucfirst($item->status)}}</td>
                                        <td>{{$item->status_note}}</td>
                                        <td>{{$item->date}}</td>
                                        <td>{{$item->approved_user ? $item->approved_user->fullname : ''}}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.leave.partials.leave-status-modal')
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
        }
    };

    $(() => Index.init());
</script>
@endsection