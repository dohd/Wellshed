@extends ('core.layouts.app')
@section ('title', "Health and Safety Objective")
@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row mb-1">
                <div class="content-header-left col-6">
                    <h4 class="content-header-title">{{ 'Health And Safety Objective' }}</h4>
                </div>
                <div class="content-header-right col-6">
                    <div class="media width-250 float-right">
                        <div class="media-body media-right text-right">
                            @include('focus.health_and_safety_objectives.partials.health-and-safety-objectives-header-buttons')
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
                                   <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Name</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{$healthAndSafetyObjective->name}}</p>
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
@endsection
