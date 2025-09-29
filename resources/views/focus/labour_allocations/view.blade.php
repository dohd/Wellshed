@extends ('core.layouts.app')

@section('title', 'Labour Allocation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Labour Allocation Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.labour_allocations.partials.labour_allocation-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">
                    <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#labour_allocationStatusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                        $details = [ 
                            'Project No' => gen4tid('PRJ-', $labour_allocation->project->tid),
                            'Customer- Branch' => $customer_branch,
                            // 'Employees' => $employee_name,
                            'Hours' => numberFormat($labour_allocation->hrs),
                            'Work Date' => $labour_allocation->date? dateFormat($labour_allocation->date) : '',
                            'Job Card' => $labour_allocation->job_card,
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                    <tr>
                        <th width="50%">{{ $key }}</th>
                        <td>{{ $val }}</td>
                    </tr>
                    @endforeach
                       {{-- <thead>
                        <th>Project</th>
                        <th>Employee Information</th>
                       </thead>
                       <tbody>
                        @foreach ($labour_allocation_item as $item)
                            <tr>
                                <td>{{ gen4tid('PRJ-',$project->tid) }}</td>
                                <td>
                                    <table>
                                        <thead>
                                            <th>Employee Name</th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><a href="{{ route('biller.labour_allocations.attach_employee', [$item->id, $item->employee_id])}}">{{ $item->employee->first_name.' '.$item->employee->last_name }}</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                       </tbody> --}}
                    </table>
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <th>Employees</th>
                        </thead>
                        <tbody>
                            @foreach ($employee as $emp)
                                <tr>
                                    
                                    <td><a href="{{ route('biller.labour_allocations.attach_employee', [$emp['id'], $emp['employee_id']])}}">{{ $emp['employee_name'] }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
