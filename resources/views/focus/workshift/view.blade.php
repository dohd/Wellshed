@extends('core.layouts.app')

@section('title', 'Workshift Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Workshift Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.workshift.partials.workshift-header-buttons')
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
                        $record = $workshift;                        
                        $details = [
                            'Workshift Name' => $record->name,
                            'Date' => dateFormat($record->created_at),
                        ];
            
                        $workshift_items = \App\Models\workshift\Workshift::where('id',$record->id)->first();
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
                        <th width="10%" class="text-center">#</th>
                        <th width="20%" class="text-center">Weekdays</th>
                        <th width="10%" class="text-center">Hours</th> 
                        <th width="10%" class="text-center">Clock In</th>    
                        <th width="10%" class="text-center">Clock Out</th>                                                        
                    </tr>
                </thead>
                <tbody>
                     @isset ($workshift_items)
                        @php ($i = 0)
                        @foreach ($workshift_items->item as $item)
                            @if ($item)
                            <tr>
                                <td class="text-center">{{ $item->id }}</td>
                                <td class="text-center">{{  $item->weekday }}</td>
                                <td class="text-center">{{ $item->hours }}</td>
                                <td class="text-center">{{ $item->clock_in }}</td>
                                <td class="text-center">{{ $item->clock_out }}</td>
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
