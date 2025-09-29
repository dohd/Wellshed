<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title', 'Submit Client Feedback')

@section('page-header')
    <h1>
        Submit Client Feedback
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> Submit Client Feedback </h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        @if($logged_in_user)
                            <div class="media-body media-right text-right">
{{--                                @include('focus.promotional_code_reservations.header-buttons')--}}
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



                                    {{ Form::open(['route' => 'save-client-feedback', 'method' => 'POST', 'id' => 'create-employee-daily-log', 'enctype' => "multipart/form-data",'files' => true  ]) }}


                                    <div class="form-group">

                                        {{-- Including Form blade file --}}
                                        @include('focus.client_feedback.form')

                                        <div class="edit-form-btn mt-3">

                                            {{ Form::submit('Submit Feedback', ['class' => 'btn btn-primary btn-md']) }}
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

