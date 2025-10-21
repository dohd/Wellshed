@extends ('core.layouts.app')

@section ('title', 'Branch Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Branch Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.branches.partials.branches-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table id="branchTbl" class="table table-lg table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $details = [
                            'Branch Name' => $branch->name,
                            'Code' => $branch->branch_code,
                            'Location' => $branch->location,
                            'Contact Name' => $branch->contact_name,
                            'Contact Phone' => $branch->contact_phone,
                            'Customer' => $branch->customer ? $branch->customer->name : ''
                        ];
                    @endphp
                    @foreach ($details as $key => $val)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection