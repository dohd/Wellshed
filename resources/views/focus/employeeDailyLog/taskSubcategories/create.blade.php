@extends ('core.layouts.app')

@section ('title',  'Create EDL Key Activity')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Create EDL Key Activity</h3>
        </div>
    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.employee-task-subcategories.store', 'method' => 'POST', 'id' => 'create-employee-daily-log']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}
                                @include("focus.employeeDailyLog.taskSubcategories.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.employee-task-subcategories.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
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
    $('#key_activity_id').select2({allowClear: true});
</script>
@endsection