@extends('core.layouts.app')

@section('title', 'Asset Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Asset Equipment Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.assetequipments.partials.assetequipments-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"></div>
        <div class="card-body">
            <table id="assetTbl" class="table table-xs table-bordered">
                <tbody>
                    @php
                        $record = $assetequipment;                        
                        $details = [
                            'Name' => $record->name,
                            'Account Name' => $record->account? $record->account->holder : '',
                            'Purchase Date' => dateFormat($record->purchase_date),
                            'Condition' => $record->condition,
                            'Manufacturer' => $record->manufacturer,
                            'Model' => $record->model,
                            'Location' => $record->location,
                            'Serial No' => $record->serial,
                            'Warranty' => $record->warranty,
                            'Warranty Expiry Date' => $record->warranty_expiry_date? dateFormat($record->warranty_expiry_date) : '',
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th width="50%">{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
