@extends ('core.layouts.app')

@section ('title', 'View | Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Equipment Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.equipments.partials.equipments-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    @if (!auth()->user()->customer_id)
                        <div class="card-header">
                            <a href="#" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#attachEquipment">
                                <i class="fa fa-bell-o" aria-hidden="true"></i> Attach Service Kits
                            </a>
                            <a href="#" class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#dettachEquipment">
                                <i class="fa fa-bell-o" aria-hidden="true"></i> Dettach Service Kits
                            </a>
                        </div>
                    @endif
                    <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="active-tab1" data-toggle="tab" href="#active1" aria-controls="active1" role="tab" aria-selected="true">Equipment Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="active-tab2" data-toggle="tab" href="#active2" aria-controls="active2" role="tab">Service Kits Attached</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " id="active-tab3" data-toggle="tab" href="#active3" aria-controls="active3" role="tab">Equipment Status Report</a>
                        </li>
                    </ul>
                    <div class="tab-content px-1 pt-1">
                        <!-- tab1 -->
                        <div class="tab-pane active in" id="active1" aria-labelledby="active-tab1" role="tabpanel">
                            <div class="card-content">
                                <div class="card-body">
                                    <table class="table table-bordered table-sm">
                                        @php
                                            $details = [ 
                                                'Equipment No' => gen4tid('Eq-', $equipment->tid),
                                                'Customer' => $equipment->customer? $equipment->customer->company : '',    
                                                'Branch' => $equipment->branch? $equipment->branch->name : '',
                                                'Equipment Category' => $equipment->category? $equipment->category->name : '',
                                                'Client Tag No' => $equipment->unique_id,
                                                'Serial No' => $equipment->equip_serial,
                                                'Make / Type' => $equipment->make_type,
                                                'Model / Model Number' => $equipment->model,
                                                'Capacity' => $equipment->capacity,
                                                'Gas / Fuel Type' => $equipment->machine_gas,
                                                'Equipment Location' => $equipment->location,
                                                'Equipment Building' => $equipment->building,
                                                'Building Floor' => $equipment->floor,
                                                'Service Rate (VAT Exc)' => numberFormat($equipment->service_rate),
                                                'Installation Date' => $equipment->install_date? dateFormat($equipment->install_date) : '',
                                                'Remark' => $equipment->note,
                                                'PM Duration(Mins)' => $equipment->pm_duration,
                                                'Status' => $equipment->status,
                                            ];
                                        @endphp
                                        @foreach ($details as $key => $val)
                                        <tr>
                                            <th width="50%">{{ $key }}</th>
                                            <td>{{ $val }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- tab2 -->
                        <div class="tab-pane in" id="active2" aria-labelledby="active-tab2" role="tabpane2">

                            <div class="card-content">
                                <div class="card-body">
                                    <table class="table table-xs table-bordered">
                                        <thead>
                                            <tr class="item_header bg-gradient-directional-blue white">
                                                <th width="30%">Service Kit Name</th>
                                                <th width="70%">Service Kit Items</th>                                    
                                            </tr>
                                        </thead>
                                        <tbody>
                                             @isset ($equipment->toolkits)
                                                @php ($i = 0)
                                                @foreach ($equipment->toolkits as $item)
                                                    @if ($item)
                                                    <tr>
                                                       
                                                        <td><a href="{{ route('biller.toolkits.show',$item['id']) }}">{{ $item['toolkit_name'] }}</a></td>
                                                        {{-- <td class="text-center">{{ $item->item }}</td> --}}
                                                        <td>
                                                            <table width="100%">
                                                                <thead>
                                                                   <th>Item Name</th>
                                                                   <th>Code</th>
                                                                   <th>UoM</th>
                                                                   <th>Quantity</th>
                                                                   <th>Cost</th>
                                                                   <th>Available Stock</th>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($item->item as $items)
                                                                    <tr>
                                                                        <td>{{$items->toolname}}</td>
                                                                        <td>{{$items->code}}</td>
                                                                        <td>{{$items->uom}}</td>
                                                                        <td>{{$items->quantity}}</td>
                                                                        <td>{{$items->cost}}</td>
                                                                        <td>{{ $items->equipment_toolkit->qty}}</td>
                                                                    </tr>
                                                                     @endforeach
                                                                </tbody>
                                                            </table>
                                                           
                                                        </td>
                                                    </tr>
                                                        @php ($i++)
                                                    @endif
                                                @endforeach
                                            @endisset
                                        </tbody>
                                    </table>
                                   
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane in" id="active3" aria-labelledby="active-tab3" role="tabpane3">

                            <div class="card-content">
                                <div class="card-header">
                                    <div class="form-group row">
                                        <div class="col-3">
                                            <label for="customer" class="font-weight-bold">Customer</label>
                                            <h4>{{$customer}}</h4>
                                        </div>
                                        <div class="col-3">
                                            <label for="customer" class="font-weight-bold">Customer Branch</label>
                                            <h4>{{$branch}}</h4>
                                        </div>
                                        <div class="col-3">
                                            <label for="equipment" class="font-weight-bold">Equipment</label>
                                            <h4>{{ gen4tid('Eq-', $equipment->tid) }}</h4>
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="card-body">
                                    <table class="table table-xs table-bordered" id="reportsTbl">
                                        <thead>
                                            <tr class="">
                                                <th width="15%">Date</th>
                                                <th width="25%">Source</th>
                                                <th>Status</th>
                                                <th>Document Type</th>                                    
                                            </tr>
                                        </thead>
                                        <tbody>
                    
                                           {{-- {{ dd($grouped) }} --}}
                                            @if($grouped)
                                                @foreach ($grouped as $i => $item)
                                                {{-- {{ dd($item) }} --}}
                                                @if (isset($item))
                                                <tr>
                                                   
                                                    <td>{{ @$item['date'] }}</td>  
                                                    <td>{{ @$item['tid'] }}</td>
                                                    <td>{{ @$item['fault'] }}</td>  
                                                    <td>{{ @$item['document_type'] }}</td>
                                                </tr>
                                                @endif  
                                            @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.equipments.partials.attach-toolkit')
        @include('focus.equipments.partials.dettach-toolkit')
    </div>
</div>
@endsection
@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
    <script>
         $('#reportsTbl').dataTable({
            columnDefs: [
                    { type: "custom-date-sort", targets: [1] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print']
            });
    </script>
@endsection
