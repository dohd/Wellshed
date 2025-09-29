@extends ('core.layouts.app')

@section ('title', 'Prospects Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Prospects Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-auto float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.prospects.partials.prospects-header-buttons')
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
                <a href="{{ route('biller.prospects.edit', [$prospect, 'page=copy']) }}" class="btn btn-warning btn-sm mr-1">
                    <i class="fa fa-clone" aria-hidden="true"></i> Copy
                </a>                
            </div>
            
            <h5 class="card-title mt-1"><b>Name:</b>&nbsp;&nbsp;{{ $prospect->name }}</h5>
        </div>
        <div class="card-body">
            <table id="prospects-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <th>Id</th>
                        
                        <td>{{ $prospect->id }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        @if ($prospect->status =='won' || 'lost')
                            <td class='text-success'>Closed
                                <span style='color:black'> || {{ $prospect->status }}</span> 
                            </td>   
                        @else
                            <td class='font-weight-bold'>Open</td>
                        @endif
                    </tr> 
                    <tr>
                        <th>Reason For Closure</th>
                        <td>{{  $prospect->reason ==null ? '---':$prospect->reason }}</td>
                    </tr> 
                    <tr>
                        <th>Name</th>
                        <td>{{  $prospect->name ==null ? '---':$prospect->name }}</td>
                    </tr>
                    <tr>
                        <th>Region</th>
                        <td>{{ $prospect->region  ==null ? '---':$prospect->region }}</td>
                    </tr>
                    <tr>
                        <th>Industry</th>
                        <td>{{ $prospect->industry  ==null ? '---':$prospect->industry }}</td>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <td>{{ $prospect->company  ==null ? '---':$prospect->company }}</td>
                    </tr>
                    
                    <tr>
                        <th>Contact</th>
                        <td>{{  $prospect->phone  ==null ? '---':$prospect->phone }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $prospect->email  ==null ? '---':$prospect->email }}</td>
                    </tr>                                 
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('focus.prospects.partials.status_modal')
@endsection
