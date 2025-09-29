@extends ('core.layouts.app')

@section('title', 'Verification Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Verification Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.verifications.partials.verification-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Leave Category' => $verification->leave_category? $verification->leave_category->title : '',
                                'Leave Status' => $verification->status,
                                'Leave Reason' => $verification->reason,
                                'Leave Duration' => $verification->qty . ' days',
                                'Start Date' => dateFormat($verification->start_date),
                                'End Date' => dateFormat($verification->end_date),
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Leave Status')
                                        <span class="text-success">{{ $val }}</span>
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
