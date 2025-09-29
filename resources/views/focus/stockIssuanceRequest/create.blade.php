@extends ('core.layouts.app')

@section ('title',  'Create Stock Issuance Request')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Edit Stock Issuance Request</h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.stockIssuanceRequest.partials.header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.stock-issuance-request.store', 'method' => 'POST', 'id' => 'create-stock-issuance-request']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}
                                @include("focus.stockIssuanceRequest.create_form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.employee-daily-log.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-2']) }}
                                    {{ Form::submit('Save', ['class' => 'btn btn-primary btn-md']) }}
                                    <div class="clearfix"></div>
                                </div>                                    
                            </div>
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

<script>

    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});







</script>
@endsection