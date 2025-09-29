@extends ('core.layouts.app')
@section ('title', 'View | Casuals Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title mb-0">Casuals Management</h3>
        </div>
        <div class="content-header-right col-md-6 col-12 mb-2">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.casuals.partials.casuals-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <fieldset class="border p-1 mb-2">
                                <legend class="w-auto float-none h5">Personal Data</legend>
                                @php
                                    $details = [
                                        'Full Name' => $casual->name,
                                        'ID Number' => $casual->id_number,
                                        'Phone Number' => $casual->phone_number,
                                        'Gender' => $casual->gender,
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <p>
                                        <span class="h4"><b>{{ $key }}: </b> {{ $val }}</span>
                                    </p>
                                @endforeach    
                                <h4>Uploads</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table id="docTbl" class="table table-bordered" width="100%">
                                            <thead>
                                                <tr class="text-center">
                                                    <th width="50%">File Caption</th>
                                                    <th>All Documents</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($casual->casual_docs as $row)
                                                    <tr class="text-center">
                                                        <td>{{ $row->caption }}</td>
                                                        <td>
                                                            @if($row->document_name)
                                                                <p>
                                                                    <a href="{{ Storage::disk('public')->url('files/casual_docs/' . $row->document_name) }}" target="_blank">
                                                                        {{ $row->document_name }}
                                                                    </a>
                                                                </p>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </fieldset>

                            @if ($casual->kin_name)
                                <fieldset class="border p-1 mb-2">
                                    <legend class="w-auto float-none h5">Next of Kin Data</legend>
                                    @php
                                        $details = [
                                            'Full Name' => $casual->name,
                                            'ID Number' => $casual->id_number,
                                            'Phone Number' => $casual->phone_number,
                                            'Gender' => $casual->gender,
                                        ];
                                    @endphp
                                    @foreach ($details as $key => $val)
                                        <p>
                                            <span class="h4"><b>{{ $key }}: </b> {{ $val }}</span>
                                        </p>
                                    @endforeach    
                                </fieldset>
                            @endif  


                            <fieldset class="border p-1 mb-2">
                                <legend class="w-auto float-none h5">Pay Set-up</legend>
                                @php
                                    $details = [
                                        'Job Category' => $casual->job_category->name,
                                        'Hourly Pay Rate' => numberFormat($casual->rate),
                                        'Work Type' => $casual->work_type,
                                        'Wage Items' => $casual->wageItems->pluck('name')->implode(', '),
                                        'Official/Business Email' => $casual->email,
                                    ];
                                @endphp
                                @foreach ($details as $key => $val)
                                    <p>
                                        <span class="h4"><b>{{ $key }}: </b> {{ $val }}</span>
                                    </p>
                                @endforeach    
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
