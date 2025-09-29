@extends('core.layouts.app')

@section('title', 'Edit | PM Report Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">PM Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contractservices.partials.contractservices-header-buttons')
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
                            {{ Form::model($contractservice, ['route' => ['biller.contractservices.update', $contractservice], 'method' => 'PATCH']) }}
                                @include('focus.contractservices.form')
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
@include('focus.contractservices.form_js')
<script>  
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    const contractService = @json($contractservice);
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    .datepicker('setDate', new Date(contractService.date));

    // remove row template
    $('#equipTbl tbody tr:eq(0)').remove();
    // assign autocomplete
    rowIndx = $('#equipTbl tbody tr').length;
    for (let i = 0; i < rowIndx; i++) {
        $('#descr-'+i).autocomplete(completeEquip(i));
    }
</script>
@endsection