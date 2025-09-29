@extends('core.layouts.app')

@section('title', 'PM Report Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">PM Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contractservices.partials.contractservices-header-buttons')
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
                            <table id="serviceTbl" class="table table-bordered table-sm mb-2">
                                @php
                                    $details = [
                                        'Customer' => $contractservice->customer? $contractservice->customer->company : '',
                                        'Branch' => $contractservice->branch? $contractservice->branch->name : '',
                                        'Contract' => $contractservice->contract? $contractservice->contract->title : '',
                                        'Task Schedule' => $contractservice->task_schedule?$contractservice->task_schedule->title : '',
                                        'Rate Amount' => amountFormat($contractservice->rate_ttl),
                                        'Bill Amount' => amountFormat($contractservice->bill_ttl),
                                        'Jobcard No' => $contractservice->jobcard_no,
                                        'Date' => dateFormat($contractservice->date),
                                        'Technician' => $contractservice->technician
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <tr>
                                        <th>{{ $key }}</th>
                                        <td>{{ $val }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            <div class="table-reponsive" style="overflow-x: scroll;">
                                <table class="table">
                                    <thead>
                                        <tr class="bg-gradient-directional-blue white">
                                            <th>#</th>
                                            <th>Equipment No</th>
                                            <th>Description</th>    
                                            <th>Location</th>   
                                            <th>Rate</th> 
                                            <th width="10%">Status</th>                                                                              
                                            <th>Billed</th>
                                            <th width="12%">Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                            
                                        @foreach ($contractservice->items as $i => $row)                                            
                                            @if ($row->equipment)
                                            <tr> 
                                                <td>{{ $i+1 }}</td>                                               
                                                <td>{{ gen4tid('Eq-', $row->equipment->tid) }}</td>
                                                <td>{{ $row->equipment->make_type }} {{ $row->equipment->capacity }} </td>                                                                                     
                                                <td>{{ $row->equipment->location }}</td>    
                                                <td>{{ numberFormat($row->equipment->service_rate) }}</td>                                       
                                                <td>{{ ucfirst($row->status) }}</td>
                                                <td>{{ $row->is_bill? 'Yes' : 'No' }}</td>
                                                <td>{{ $row->note }}</td>
                                            </tr>  
                                            @endif                                                      
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
@endsection