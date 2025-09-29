<div class="modal" id="AddProjectModal" role="dialog" aria-labelledby="data_project" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title content-header-title" id="data_project">Project Management</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"> 
                {{ Form::open(['route' => 'biller.projects.store', 'id' => 'data_form_project']) }}   
                 @include('focus.projects.form')
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submit-data_project">
                        Create Project
                    </button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>