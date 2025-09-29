@extends ('core.layouts.app')

@section('title', trans('labels.backend.warehouses.management'))

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-6">
                <h3 class="content-header-title">{{ trans('labels.backend.warehouses.view') }}</h3>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.warehouses.partials.warehouses-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body pl-3 pr-3">
                                <div class="row">
                                    <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                        <p>{{ trans('warehouses.title') }} </p>
                                    </div>
                                    <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                        <p> 
                                            {{ $warehouse->title }} 
                                            <a class="btn btn-purple round" href="{{ route('biller.products.index', ['warehouse_id' => $warehouse->id]) }}" title="products">
                                                <i class="fa fa-list"></i>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                        <p>{{ trans('warehouses.extra') }}</p>
                                    </div>
                                    <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                        <p> {{ $warehouse->extra }}</p>
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
