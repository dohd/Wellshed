@extends ('core.layouts.app')

@section ('title', 'CallList Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Individual CallList</h4>
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
           
            
            <h5 class="card-title mt-1"><b>Name:</b>&nbsp;&nbsp;{{ $calllist->title }}</h5>
        </div>
        <div class="card-body">
            <table id="calllists-table" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <th>Id</th>
                        
                        <td>{{ $calllist->id }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{  $calllist->category }}</td>
                    </tr>
                    <tr>
                        <th>Prospects To Call</th>
                        <td>{{ $calllist->prospects_number }}</td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td>{{ $calllist->start_date }}</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>{{ $calllist->end_date }}</td>
                    </tr>
                    
                                                   
                </tbody>
            </table>
        </div>
    </div>

    

    
</div>

@endsection
