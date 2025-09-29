@extends ('core.layouts.app')

@section ('title',  'Account Charges Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Account Charges Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.charges.partials.charges-header-buttons')
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card round">
                    <div class="card-content">
                        <div class="card-body ">
                            {{ Form::open(['route' => 'biller.charges.store', 'method' => 'post', 'class' => 'pl-1']) }}
                                @include("focus.charges.form")
                                <div class="ml-1">
                                    {{ link_to_route('biller.charges.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md round']) }}
                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md round']) }}
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
