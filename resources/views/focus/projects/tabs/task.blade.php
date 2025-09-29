<div class="tab-pane" id="tab_data3" aria-labelledby="tab3" role="tabpanel">
    <div class="btn-group">
        <button type="button" class="btn btn-info float-right mr-2" data-toggle="modal" data-target="#AddTaskModal">
            <i class="fa fa-plus-circle"></i> New Task
        </button> 
        <a href="{{ route('biller.import.general', 'tasks') }}?project_id={{$project->id}}" class="btn btn-success  btn-lighten-2">
            <i class="fa fa-upload" aria-hidden="true"></i> Import
        </a>        
    </div>
    <div class="card-body">
        <table id="tasks-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Milestone</th>
                    <th>Task Title</th>
                    <th>Start Date</th>
                    <th>Duration(days)</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Progress</th>
                    <th>{{ trans('labels.general.actions') }}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
