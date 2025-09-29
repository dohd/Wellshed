@extends ('core.layouts.app')

@section ('title', 'Tickets Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tickets Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.leads.partials.leads-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="button-group">
                <a href="#" class="btn btn-info btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Status
                </a>
                <a href="{{ route('biller.leads.edit', [$lead, 'page=copy']) }}" class="btn btn-warning btn-sm mr-1">
                    <i class="fa fa-clone" aria-hidden="true"></i> Copy
                </a> 
                <a href="#" class="btn btn-danger btn-sm mr-1" data-toggle="modal" data-target="#reminderModal">
                    <i class="fa fa-bell-o" aria-hidden="true"></i> Add Reminder
                </a>     
                    
                @if (!$days)
                <span class="text-success float-right">Notification Not Set</span>
                @elseif ($days > 10)
                <span class="text-primary float-right"><b>{{$days}}</b>: Days Remaining</span>
                @elseif($days < 10)
                <span class="text-danger float-right"><b>{{$days}}</b>: Days Remaining</span>
                
                @endif    
            </div>
            
            <h5 class="card-title mt-1"><b>Title:</b>&nbsp;&nbsp;{{ $lead->title }}</h5>
        </div>
        <div class="card-body">
            <table id="leads-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <th>Reference</th>
                        @php
                            $prefixes = prefixesArray(['lead'], $lead->ins);
                        @endphp
                        <td>{{ gen4tid("{$prefixes[0]}-", $lead->reference) }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        @if ($lead->status)
                            <td class='text-success'>Closed
                                <span style='color:black'> || {{ $lead->reason }}</span> 
                            </td>
                        @else
                            <td class='font-weight-bold'>Open</td>
                        @endif
                    </tr> 
                    <tr>
                        <th>Client Name</th>
                        <td>{{ $lead->customer ? $lead->customer->name : $lead->client_name }}</td>
                    </tr>
                    <tr>
                        <th>Client Ref / Callout ID</th>
                        <td>{{ $lead->client_ref }}</td>
                    </tr>
                    @if ($lead->branch)
                        <tr>
                            <th>Client Branch</th>
                            <td>{{ $lead->branch->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Client Contact</th>
                        <td>{{ $lead->customer ? $lead->customer->phone : $lead->client_contact }}</td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td>{{ $lead->customer? $lead->customer->email : $lead->client_email }}</td>
                    </tr> 
                    <tr>
                        <th>Client Address</th>
                        <td>{{ $lead->customer? $lead->customer->address : $lead->client_address }}</td>
                    </tr>   
                    <tr>
                        <th>Callout Date</th>
                        <td>{{ dateFormat($lead->date_of_request) }}</td>
                    </tr>                    
                    <tr>
                        <th>Requested By</th>
                        <td>{{ $lead->assign_to }}</td>
                    </tr>
                    <tr>
                        <th>Source</th>
                        <td>{{ $lead->source }}</td>
                    </tr>
                    <tr>
                        <th>Note</th>
                        <td>{!! $lead->note !!}</td>
                    </tr>
                    <tr>
                        <th>Created at</th>
                        <td>{{ dateFormat($lead->created_at) }}</td>
                    </tr>
                    <tr>
                        <th>Reminder Start Date</th>
                        <td>{{ $lead->reminder_date }}</td>
                    </tr>
                    <tr>
                        <th>Event Date</th>
                        <td>{{ $lead->exact_date}}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('focus.leads.partials.status_modal')
@include('focus.leads.partials.reminder_modal')
@endsection
