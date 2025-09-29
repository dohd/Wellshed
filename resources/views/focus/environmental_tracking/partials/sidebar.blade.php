<div class="sidebar-left" style="width: 250px;">
    <div class="sidebar">
        <div class="sidebar-content">
            <div class="row">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-content">
                                @permission('create-project')
                                    <div class="form-group form-group-compose text-center">
                                        <button type="button" class="btn btn-success btn-block" id="addt" data-toggle="modal" data-target="#AddProjectModal">
                                            {{trans('projects.new_project')}}
                                        </button>
                                    </div> 
                                @endauth
        
                                <div class="sidebar-todo-container">
                                    <h6 class="text-muted text-bold-500 my-1">{{trans('general.messages')}}</h6>
                                    <div class="list-group list-group-messages">
                                        <a href="{{route('biller.dashboard')}}" class="list-group-item list-group-item-action border-0">
                                            <i class="icon-home mr-1"></i>
                                            <span>{{trans('navs.frontend.dashboard')}}</span>
                                        </a>
                                        <a href="{{route('biller.todo')}}" class="list-group-item list-group-item-action border-0">
                                            <i class="icon-list mr-1"></i>
                                            <span>{{trans('general.tasks')}}</span>
                                            <span class="badge badge-secondary badge-pill float-right">8</span>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action border-0">
                                            <i class="icon-bell mr-1"></i>
                                            <span>{{trans('general.messages')}}</span>
                                            <span class="badge badge-danger badge-pill float-right">3</span> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                        
           
        </div>
    </div>
</div>