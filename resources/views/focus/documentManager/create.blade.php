@extends ('core.layouts.app')

@section ('title',  'Create Document Tracker')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">

            <div class="content-header-left col-6">
                <h3 class="mb-0">Create Document Tracker</h3>
            </div>

            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.documentManager.header-buttons')
                    </div>
                </div>
            </div>


        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border-radius: 8px;">
                        <div class="card-content">
                            <div class="card-body">
                                {{--                            {{ Form::open(['route' => 'biller.lead-sources.store', 'method' => 'POST', 'id' => 'create-employee-daily-log']) }}--}}
                                <div class="form-group">

                                    @include('focus.documentManager.form')


                                </div>
                                {{--                            {{ Form::close() }}--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


