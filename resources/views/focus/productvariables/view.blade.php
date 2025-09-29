@extends ('core.layouts.app')
@section ('title', trans('labels.backend.productvariables.management') . ' | ' . trans('labels.backend.productvariables.create'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="content-header-title">{{ trans('labels.backend.productvariables.view') }}</h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.productvariables.partials.productvariables-header-buttons')
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
                            <table id="variableTbl" class="table table-bordered mb-2">
                                @php
                                    $details = [
                                        'Title' => $productvariable->title,
                                        'Code' => $productvariable->code,
                                        'Unit Type' => $productvariable->unit_type,
                                        'Ratio (per base unit)' => numberFormat($productvariable->base_ratio),
                                        'Count Type' => $productvariable->count_type
                                    ];
                                @endphp
                                <tbody>                    
                                    @foreach ($details as $key => $val)
                                        <tr>
                                            <th width="50%">{{ $key }}</th>
                                            <td>{{ $val }}</td>
                                        </tr> 
                                    @endforeach                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
