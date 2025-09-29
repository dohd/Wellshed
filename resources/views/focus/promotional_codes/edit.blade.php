<!DOCTYPE html>

@extends ('core.layouts.app')

@include('tinymce.scripts')

@section ('title', 'Edit Promotional Code')

@section('page-header')
    <h1>
        Edit Promotional Code
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0"> Edit Promotional Code </h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.promotional_codes.header-buttons')
                        </div>

                    </div>
                </div>
            </div>
            <div class="content-body mt-1">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">

                                    {{ Form::open(['route' => ['biller.promotional-codes.update', $promotionalCode->id], 'method' => 'PUT', 'id' => 'create-employee-daily-log', 'enctype' => "multipart/form-data"]) }}

                                    <div class="form-group">

                                        {{-- Including Form blade file --}}
                                        @include('focus.promotional_codes.form')

                                        <div class="edit-form-btn mt-3">
                                            {{ link_to_route('biller.promotional-codes.index', 'Cancel', [], ['class' => 'btn btn-secondary btn-md']) }}
                                            {{ Form::submit('Update', ['class' => 'btn btn-primary btn-md']) }}
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

