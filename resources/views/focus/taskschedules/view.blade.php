@extends('core.layouts.app')

@section('title', 'View | Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Schedule Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.taskschedules.partials.taskschedule-header-buttons')
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
                                $contract = $taskschedule->contract;
                                $contract_title = $contract? $contract->title : '';
                                $customer_title = $contract->customer? $contract->customer->company : ''; 
                                
                                $details = [
                                    'Schedule Title' => $taskschedule->title,
                                    'Equipments' => '',
                                    'Customer Contract' => "{$contract_title} - {$customer_title}", 
                                    'Schedule Date (Start - End)' => dateFormat($taskschedule->start_date) . ' || ' . dateFormat($taskschedule->end_date),
                                    'Actual Date (Start - End)' => dateFormat($taskschedule->actual_startdate) . ' || ' . dateFormat($taskschedule->actual_enddate),
                                    'Service Rate' => numberFormat($taskschedule->equipments->sum('service_rate'))
                                ];
                            @endphp
                            <table class="table table-bordered table-sm mb-2">
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th width="50%">{{ $key }}</th>
                                        <td>
                                            @if ($key == 'Equipments')
                                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="{{ $taskschedule->equipments->count()? '#statusModal' : '' }}">
                                                    <i class="fa fa-clone" aria-hidden="true"></i> Copy
                                                </a>      
                                                <a class="btn btn-purple btn-sm" href="{{ route('biller.equipments.index', ['customer_id' => $contract->customer_id, 'schedule_id' => $taskschedule->id]) }}" title="equipments">
                                                    <i class="fa fa-list"></i> List
                                                </a>  
                                            @else
                                                {{ $val }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th width="50%">Equipment Count</th>
                                    <td>{{ $taskschedule->equipments->count() }}</td>
                                </tr>
                                <tr>
                                    <th width="50%">Branch Service Done</th>
                                    <td><span id="branch_service_done"></span></td>
                                </tr>
                            </table>

                            <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#branch" role="tab" aria-controls="branch" aria-selected="false">
                                        Branches
                                    </a>
                                </li>     
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#" role="tab" aria-controls="" aria-selected="false">
                                        
                                    </a>
                                </li>              
                            </ul>
                            <div class="tab-content px-1 p-1" id="myTabContent">
                                <div class="tab-pane fade show active" id="branch" role="tabpanel" aria-labelledby="branch-tab">
                                    <div class="table-reponsive">
                                        <table class="table" id="branchTbl">
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
                                                        $branch_equip_ids = $branch->taskschedule_equipments->pluck('equipment_id')->toArray();
                                                        $branch_serviced_equip_ids = $branch->service_contract_items->pluck('equipment_id')->toArray();
                                                        // count
                                                        $unit_count = count($branch_equip_ids);
                                                        $serviced_count = count($branch_serviced_equip_ids);
                                                        $unserviced_count = count(array_diff($branch_equip_ids, $branch_serviced_equip_ids));
                                                        $percent_done = round(div_num($serviced_count, $unit_count) * 100);
                                                    @endphp                                           
                                                    <tr>
                                                        <td>{{ $i+1 }}</td>
                                                        <td>
                                                            @if ($unserviced_count)
                                                                <b>{{ $branch->name }}<b>
                                                            @else
                                                                {{ $branch->name }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($unserviced_count)
                                                                <b>{{ $unit_count }}<b>
                                                            @else
                                                                {{ $unit_count }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($serviced_count)
                                                                {{ $serviced_count }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($unserviced_count)
                                                                <b>{{ $unserviced_count }}</b>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$percent_done)
                                                                <span class="text-danger"><b>{{ $percent_done }}<b><span>
                                                            @elseif ($percent_done < 100)
                                                                <span class="text-primary"><b>{{ $percent_done }}<b><span>
                                                            @else
                                                                {{ $percent_done }}
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
@include('focus.taskschedules.partials.copy_modal')
@endsection

@section('after-scripts')
<script>
    let serviced = 0;
    let unserviced = 0;
    let branchCount = 0;
    $('#branchTbl tbody tr').each(function() {
        let serviceDone = $(this).find('td:eq(5)').text();
        if (serviceDone > 0) serviced++;
        else unserviced++;
        branchCount++;
    });

    const text = `
        <p>serviced: <b>${serviced}</b> / ${branchCount} <span class="ml-2"><b>${accounting.formatNumber(serviced/branchCount*100, 2)}%</b><span><p>
        <p>unserviced: <b>${unserviced}</b> / ${branchCount} <span class="ml-2"><b>${accounting.formatNumber(unserviced/branchCount*100, 2)}%</b><span><p>
    `;
    $('#branch_service_done').html(text);
</script>
@endsection
