@extends ('core.layouts.app')

@section ('title', 'Business Account Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Business Account Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.tenants.partials.tenants-header-buttons')
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
            </div>
        </div>
        <div class="card-body">
            <h6>Business Info</h6>
            <table class="table table-sm table-bordered mb-2" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $details = [
                            'Business Name' => $tenant->cname,
                            'Street Address' => $tenant->address,
                            'Country' => $tenant->country,
                            'Post Box' => $tenant->postbox,
                            'Email Address' => $tenant->email,
                            'Phone Number' => $tenant->phone,
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th width="40%">{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>

            <h6>User Info</h6>
            <table class="table table-sm table-bordered mb-2" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $details = [
                            'First Name' => @$user->first_name,
                            'Last Name' => @$user->last_name,
                            'User Email' => @$user->email,
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th width="40%">{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
            
            <h6>Package Info</h6>
            <table class="table table-sm table-bordered mb-2" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $package = $tenant->package;
                        $details = [
                            'Account Plan' => $service->name,
                            'Package Cost' => numberFormat(@$package->cost),
                            'Maintenance Cost' => numberFormat(@$package->maintenance_cost),
                            'Extras Cost' => numberFormat(@$package->extras_cost),
                            'Total Cost' => numberFormat(@$package->total_cost),
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th width="40%">{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('focus.tenants.partials.status_modal')
@endsection