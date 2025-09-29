<div class="tab-pane" id="tab_data11" aria-labelledby="tab11" role="tabpanel">
    <div class="card">
        <div class="card-header mb-0">
            <h4 class="card-title">{{trans('projects.users')}}</h4>
            <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
            <div class="heading-elements">
                <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                    <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                    <li><a data-action="close"><i class="ft-x"></i></a></li>
                </ul>
            </div>
        </div>

        <div class="card-content collapse show">
            <div class="card-content">
                <div class="card-body  py-0 px-0">
                    <div class="list-group">
                        @foreach ($project->users->unique('id') as $i => $row)
                            <a href="javascript:" class="list-group-item">
                                <div class="media">
                                    <div class="media-left pr-1">
                                        <span class="avatar avatar-sm">
                                            <img src="{{ Storage::disk('public')->url('app/public/img/users/' . @$row->picture) }}">
                                        </span>
                                    </div>
                                    <div class="media-body w-25">
                                        <h6 class="media-heading mb-0 text-center" style="font-size:1.5em;">{{ $i+1 }}. {{ $row->fullname }}</h6>
                                    </div>
                                    <div class="media-body w-75">
                                        <h6 class="media-heading mb-0" style="font-size:1.5em;">{{ @$row->role->name }}</h6>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
