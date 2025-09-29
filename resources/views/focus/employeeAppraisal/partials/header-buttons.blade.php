<div class="btn-group">
    @permission('manage-employee-appraisal')
        <a href="{{ route('biller.employee_appraisals.index') }}" class="btn btn-info  btn-lighten-2">
            <i class="fa fa-list-alt"></i> {{ trans('general.list') }}
        </a>
    @endauth

    @permission('create-employee-appraisal')
        <a href="{{ route('biller.employee_appraisals.create') }}" class="btn btn-pink  btn-lighten-3">
        <i class="fa fa-plus-circle"></i> Create
        </a>
    @endauth
</div>