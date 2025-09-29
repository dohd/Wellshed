@extends ('core.layouts.app')

@section('title', 'Explore | CallList Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Individual Call List</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.prospects.partials.prospects-header-buttons')
            </div>
        </div>
    </div>
  
    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                  
                        @include('focus.prospects.calllist.allocationform')
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
