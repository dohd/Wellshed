@extends ('core.layouts.app')

@section('title', 'Leave Category Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Leave Category Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.leave_category.partials.leave-category-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $gender = '';
                            switch ($leave_category->gender) {
                                case 'a': $gender = 'All'; break;
                                case 'm': $gender = 'Male'; break;
                                case 'f': $gender = 'Female'; break;
                            }
                            $details = [
                                'Title' => $leave_category->title,
                                'Gender' => $gender,
                                'Policy' => $leave_category->policy,
                                'No of Days' => $leave_category->qty,
                                'Payable Leave' => $leave_category->is_payable? 'Yes' : 'No',
                                'Encashed Leave' => $leave_category->is_encashed? 'Yes' : 'No'
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
