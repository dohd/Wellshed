{{-- @extends ('core.layouts.app')

@section ('title', 'Prospects Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Prospects Follow up Management</h4>
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
            
            
            <h5 class="card-title mt-1"><b>Name:</b>&nbsp;&nbsp;{{ $prospectcallresolved->prospect->name }}</h5>
        </div>
        <div class="card-body">
            <table id="prospects-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <th>Id</th>
                        
                        <td>{{ $prospectcallresolved->prospect->id }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        @if ($prospectcallresolved->prospect->status =='won' || 'lost')
                            <td class='text-success'>Closed
                                <span style='color:black'> || {{ $prospectcallresolved->prospect->status }}</span> 
                            </td>   
                        @else
                            <td class='font-weight-bold'>Open</td>
                        @endif
                    </tr> 
                    <tr>
                        <th>Reason For Closure</th>
                        <td>{{  $prospectcallresolved->prospect->reason ==null ? '---':$prospectcallresolved->prospect->reason }}</td>
                    </tr> 
                    <tr>
                        <th>Name</th>
                        <td>{{  $prospectcallresolved->prospect->name ==null ? '---':$prospectcallresolved->prospect->name }}</td>
                    </tr>
                    <tr>
                        <th>Region</th>
                        <td>{{ $prospectcallresolved->prospect->region  ==null ? '---':$prospectcallresolved->prospect->region }}</td>
                    </tr>
                    <tr>
                        <th>Industry</th>
                        <td>{{ $prospectcallresolved->prospect->industry  ==null ? '---':$prospectcallresolved->prospect->industry }}</td>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <td>{{ $prospectcallresolved->prospect->company  ==null ? '---':$prospectcallresolved->prospect->company }}</td>
                    </tr>
                    
                    <tr>
                        <th>Contact</th>
                        <td>{{  $prospectcallresolved->prospect->phone  ==null ? '---':$prospectcallresolved->prospect->phone }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $prospectcallresolved->prospect->email  ==null ? '---':$prospectcallresolved->prospect->email }}</td>
                    </tr>                                 
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('focus.prospects.partials.status_modal')
@endsection --}}
