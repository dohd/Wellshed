<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title', 'Self Enroll to Gig Code')

@section('page-header')
    <h1>
        Self Enroll to Gig Code
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> Self Enroll to Gig Code  </h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        @if($logged_in_user)
                            <div class="media-body media-right text-right">
                                @include('focus.promotional_code_reservations.header-buttons')
                            </div>
                        @endif


                    </div>
                </div>
            </div>
            <div class="content-body mt-1">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">


                                    
                                        {{ Form::open(['route' => 'self_enroll.store', 'method' => 'POST', 'id' => 'create-employee-daily-log']) }}
                            

                                    <div class="form-group">

                                        
                                            @include('focus.promotional_code_reservations.self_enroll_form')
    

                                        <div class="edit-form-btn mt-3">
                                            {{ link_to_route('biller.promotional-codes.index', 'Cancel', [], ['class' => 'btn btn-secondary btn-md mr-1']) }}
                                            {{ Form::submit('Submit', ['class' => 'btn btn-primary btn-md']) }}
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
    </div>
@endsection

