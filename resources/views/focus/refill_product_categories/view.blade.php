@extends ('core.layouts.app')
@section ('title', 'View | ' . trans('labels.backend.productcategories.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="mb-0">{{ trans('labels.backend.productcategories.view') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.refill_product_categories.partials.productcategories-header-buttons')
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
                                    <p>{{trans('productcategories.title')}} </p>
                                </div>
                                <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                    <p>   {{$refill_product_category->title}} </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                    <p>{{trans('productcategories.extra')}}</p>
                                </div>
                                <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                    <p> {{$refill_product_category->extra}}</p>
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
