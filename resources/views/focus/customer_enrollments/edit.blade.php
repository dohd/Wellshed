@extends ('core.layouts.app')

@section ('title', 'Edit Customer Enrollment')

@section('page-header')
    <h1>
        <small>Edit Customer Enrollment</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Customer Enrollment</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customer_enrollments.partials.customer_enrollments-header-buttons')
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
                                    {{ Form::model($customer_enrollment, ['route' => ['biller.customer_enrollments.update', $customer_enrollment], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.customer_enrollments.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.customer_enrollments.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->

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
