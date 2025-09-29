<div class="tab-pane" id="tab_data4" aria-labelledby="tab4" role="tabpanel">
    {{-- <button type="button" class="btn btn-info float-right mr-2" data-toggle="modal"
            data-target="#AddLogModal">
            <i class="fa fa-plus-circle"></i> Project Log
    </button> --}}
    <div class="card-body">
        <table id="log-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>#</th>
                <th>{{ trans('general.date') }}</th>
                <th>{{ trans('projects.users') }}</th>
                <th>{{ trans('general.description') }}</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>