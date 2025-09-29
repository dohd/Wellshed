@extends ('core.layouts.app')

@section ('title', 'Vendor Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Vendor Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.client_vendors.partials.client-vendors-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h6 class="">Business Info</h6>
            <div class="table-responsive">
                <table id="vendorsTbl" class="table table-lg table-bordered" cellspacing="0" width="100%">
                    <tbody>
                        @php
                            $details = [
                                'Company' => $client_vendor->company,
                                'Supplier Name' => $client_vendor->name,
                                'Phone' => $client_vendor->phone,
                                'Email' => $client_vendor->email,
                                'Street Address' => $client_vendor->address,
                                'Post Box' => $client_vendor->postbox,
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

            <h6 class="">User Info</h6>
            <div class="table-responsive">
                <table id="vendorsTbl" class="table table-lg table-bordered" cellspacing="0" width="100%">
                    <tbody>
                        @php
                            $details = [
                                'First Name' => @$client_vendor->user->first_name,
                                'Last Name' => @$client_vendor->user->last_name,
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
</div>
@endsection