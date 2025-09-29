@extends ('core.layouts.app')

@section ('title', 'View | Contract Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Contract Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contracts.partials.contracts-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            @php
                                $details = [
                                    'Contract No' => $contract->tid,
                                    'Title' => $contract->title,
                                    'Customer' => $contract->customer? $contract->customer->company : '',
                                    'Amount' => numberFormat($contract->amount),
                                    'Start Date' => dateFormat($contract->start_date),
                                    'End Date' => dateFormat($contract->end_date),
                                    'Contract Period (years)' => $contract->period,
                                    'Per Schedule Period (months)' => $contract->schedule_period,
                                    'Equipment Count' => $contract->equipments->count(),
                                ];
                            @endphp
                            <table class="table table-bordered table-sm mb-3">
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="50%">{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th width="50%">Branch Count</th>
                                    <td>{{ $branches->count() }}</td>
                                </tr>
                            </table>

                            {{-- tab menu --}}
                            <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">
                                        Task Schedule
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">
                                        Equipments
                                    </a>
                                </li>    
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#branch" role="tab" aria-controls="branch" aria-selected="false">
                                        Branches
                                    </a>
                                </li>                 
                            </ul>
                            
                            <div class="tab-content px-1 p-1" id="myTabContent">
                                <!-- Schedule tab -->
                                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                    <div class="table-reponsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Title</th>
                                                    <th>Start Date</th>
                                                    <th>End Date</th>
                                                    <th>Actual Start Date</th>
                                                    <th>Actual End Date</th>
                                                    <th>Unit Count</th>
                                                    <th>Serviced Units</th>
                                                    <th>Unserviced Units</th>
                                                    <th>Service Done (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>                                                
                                                @foreach ($contract->task_schedules as $i => $row)  
                                                    @php
                                                        $scheduleEquipIds = $row->contract_equipments->pluck('equipment_id')->toArray();
                                                        $servicedEquipIds = $row->contract_service_items->whereIn('equipment_id', $scheduleEquipIds)
                                                            ->pluck('equipment_id')
                                                            ->toArray();
                                                        // count
                                                        $numScheduleUnits = count($scheduleEquipIds);
                                                        $numServicedUnits = count($servicedEquipIds);
                                                        $numUnservicedUnits = count(array_diff($scheduleEquipIds, $servicedEquipIds));
                                                        $percServiced = round(div_num($numServicedUnits, $numScheduleUnits) * 100);
                                                    @endphp                                                  
                                                    <tr>
                                                        <td>{{ $i+1 }}</td>
                                                        <td>{{ $row->title }}</td>
                                                        <td>{{ dateFormat($row->start_date) }}</td>
                                                        <td>{{ dateFormat($row->end_date) }}</td>
                                                        <td>{{ $row->actual_startdate? dateFormat($row->actual_startdate) : '' }}</td>
                                                        <td>{{ $row->actual_enddate? dateFormat($row->actual_enddate) : '' }}</td>
                                                        <td>{{ $numScheduleUnits ?: '' }}</td>
                                                        <td>{{ $numServicedUnits ?: '' }}</td>
                                                        <td>{{ $numUnservicedUnits ?: '' }}</td>
                                                        <td>{{ $percServiced?: '' }}</td>
                                                    </tr>
                                                @endforeach                                                    
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- equipments tab -->
                                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                    <div class="table-reponsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Equipment No.</th>
                                                    <th>Serial No</th>
                                                    <th>Type</th>
                                                    <th>Branch</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                            <tbody>   
                                                @php $i = 0; @endphp
                                                @foreach ($contract->equipments->sortBy('branch_id') as $row)                                            
                                                    <tr>
                                                        <td>{{ $i+1 }}</td>
                                                        <td>{{ gen4tid('Eq-', $row->tid) }}</td>
                                                        <td>{{ $row->equip_serial }}</td>
                                                        <td>{{ $row->make_type }}</td>
                                                        <td>{{ $row->branch? $row->branch->name : '' }}</td>
                                                        <td>{{ $row->location }}</td>
                                                    </tr>     
                                                    @php $i++ @endphp
                                                @endforeach                                                  
                                            </tbody>
                                        </table>
                                    </div>
                                </div>      
                                
                                <!-- branches tab -->
                                <div class="tab-pane fade" id="branch" role="tabpanel" aria-labelledby="branch-tab">
                                    <div class="table-reponsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Branch Name</th>
                                                    <th>Unit Count</th>
                                                    <th>Serviced Units</th>
                                                    <th>Unserviced Units</th>
                                                    <th>Service Done (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($branches as $i => $branch) 
                                                    @php
                                                        $branchEquipIds = $branch->equipments()->whereHas('contract_equipment')->pluck('equipments.id')->toArray();
                                                        $branchServicedEquipIds = $branch->equipments()->whereHas('service_item')->pluck('equipments.id')->toArray();
                                                        // count
                                                        $numBranchEquip = count($branchEquipIds);
                                                        $numBranchServicedEquip = count($branchServicedEquipIds);
                                                        $numBranchUnservicedEquip = count(array_diff($branchEquipIds, $branchServicedEquipIds));
                                                        $percServiced = round(div_num($numBranchServicedEquip, $numBranchEquip) * 100);
                                                    @endphp                                           
                                                    <tr>
                                                        <td>{{ $i+1 }}</td>
                                                        <td>
                                                            @if ($numBranchUnservicedEquip)
                                                                <b>{{ $branch->name }}<b>
                                                            @else
                                                                {{ $branch->name }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($numBranchUnservicedEquip)
                                                                <b>{{ $numBranchEquip }}<b>
                                                            @else
                                                                {{ $numBranchEquip }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $numBranchServicedEquip ?: '' }}</td>
                                                        <td>{{ $numBranchUnservicedEquip ?: '' }}</td>
                                                        <td>
                                                            @if (!$percServiced)
                                                                <span class="text-danger"><b>{{ $percServiced }}<b><span>
                                                            @elseif ($percServiced < 100)
                                                                <span class="text-primary"><b>{{ $percServiced }}<b><span>
                                                            @else
                                                                {{ $percServiced }}
                                                            @endif
                                                        </td>
                                                    </tr>                                                        
                                                @endforeach                                          
                                            </tbody>
                                        </table>
                                    </div>
                                </div>   
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection