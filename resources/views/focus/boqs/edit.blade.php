@extends ('core.layouts.app')

@section ('title', 'Edit BoQ')

@section('page-header')
    <h1>
        Manage BoQ
        <small>Edit BoQ</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit BoQ</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.boqs.partials.boqs-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div id="message"></div>
                            </div>

                            <div class="card-content">

                                <div class="card-body">
                                    {{-- {{ Form::model($boqs, ['route' => ['biller.boqs.update', $boqs], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'boqForm']) }} --}}
                                    <form id="boqForm" method="POST">
                                        @method('PATCH')
                                        @csrf
                                        <div class="form-group">
                                            {{-- Including Form blade file --}}
                                            @include("focus.boqs.form")
                                        </div><!--form-group-->
                                    </form>
                                    {{-- {{ Form::close() }} --}}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
    @include('focus.boqs.edit_js')
@endsection