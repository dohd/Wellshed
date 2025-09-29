@extends ('core.layouts.app')

@section('title', 'Holiday Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Holiday Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.holiday_list.partials.holiday-list-header-buttons')
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
                                'Title' => $holiday_list->title,
                                'Date' => dateFormat($holiday_list->date),
                                'Note' => $holiday_list->note,
                                'Is Recurrent' => $holiday_list->is_recurrent? 'Yes' : 'No'
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
