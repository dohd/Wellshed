@extends ('core.layouts.app')

@section ('title', trans('labels.backend.projects.management'))

@section('content')
    <div class="content-wrapper">
        <!-- Header -->
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Project Management</h4>
            </div>
            <div class="col-6">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
                </div>
            </div>
        </div>
        <!-- End Header -->

        <!-- Left sidebar -->
        @include('focus.projects.partials.sidebar')
        <!-- End Left sidebar -->

        <!-- Content -->
        <div class="content-right" style="width: calc(100% - 270px)">
            <div class="content-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-control select2" id="customerFilter" data-placeholder="Search Customer">
                                    <option value=""></option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control select2" id="branchFilter" data-placeholder="Search Branch">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="custom-select" id="projectStatus">
                                    <option value="">-- Select Status --</option>
                                    @foreach ($statuses as $row)
                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                        <br>
                        <div class="row">
                            <div class="ml-2">{{ trans('general.search_date')}} </div>
                            <div class="col-md-2">
                                <input type="text" name="start_date" id="start_date" class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="end_date" id="end_date" class="form-control datepicker form-control-sm" autocomplete="off" />
                            </div>
                            <div class="col-md-2">
                                <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                            </div>
                        </div>  
                        <hr>
                        <table id="projectsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>#Project No.</th>
                                    <th>#Quote/PI</th>
                                    <th>Name</th>
                                    <th>WIP Account</th>
                                    <th>Exp G.P(%)</th>
                                    <th>Overall Project (%)</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Job Hrs</th>
                                    <th>Start</th>
                                    <th>Deadline</th>
                                    <th>{{ trans('general.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-success font-large-1">
                                        <i class="fa fa-spinner spinner"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Content -->
    </div>

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    {{-- <input type="hidden" id="loader_url" value="{{route('biller.projects.load')}}"> --}}
    @include('focus.projects.modal.project_new')
    @include('focus.projects.modal.status_modal')
    @include('focus.projects.modal.project_view')
@endsection
@section('after-styles')
    {{ Html::style('core/app-assets/css-'.visual().'/pages/app-todo.css') }}
    {{ Html::style('core/app-assets/css-'.visual().'/plugins/forms/checkboxes-radios.css') }}
    {!! Html::style('focus/css/bootstrap-colorpicker.min.css') !!}
@endsection
@section('after-scripts')
@include('focus.projects.index_js')
@endsection
