@extends ('core.layouts.app')

@section ('title',  'Assign Key Performance Indicators')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Assign Key Performance Indicators</h3>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeDailyLog.partials.edl-header-buttons')
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
                            {{ Form::open(['route' => 'biller.edl-subcategory-allocations.store', 'method' => 'POST', 'id' => 'create-edl-subcategory-allocation']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}
                                @include("focus.employeeDailyLog.assignSubcategories.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.edl-subcategory-allocations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1 round']) }}
                                    {{ Form::submit('Assign Key Performance Indicators', ['class' => 'btn btn-primary btn-md round']) }}
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
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#purchase_date').datepicker('setDate', new Date());
    $('#warranty_expiry_date').datepicker('setDate', new Date());
</script>
@endsection