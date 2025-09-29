@extends('core.layouts.app')

@section('title', 'Service Kit Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Service Kit Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.toolkit.partials.toolkit-header-buttons')
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
                        $record = $toolkit;                        
                        $details = [
                            'Service Kit Name' => $record->toolkit_name,
                            'Date' => dateFormat($record->created_at),
                        ];
            
                        $toolkit_items = \App\Models\toolkit\Toolkit::where('id',$record->id)->first();
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

    <div class="card">
        <div class="card-body">
            <table class="table table-xs table-bordered">
                <thead>
                    <tr class="item_header bg-gradient-directional-blue white">
                        <th width="10%">#Id</th>
                        <th width="20%">Product Name</th>
                        <th width="10%">Issued Qty</th> 
                        <th width="10%">Cost</th>                                                            
                    </tr>
                </thead>
                <tbody>
                     @isset ($toolkit_items)
                        @php ($i = 0)
                        @foreach ($toolkit_items->item as $item)
                            @if ($item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{  $item->toolname }}---{{$item->code}}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->cost }}</td>
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
@endsection
