@section('title',company()->seo_title)
@section('menu')
    @include('general.partial.menu',array('home'=>''))
@endsection
@extends('general.layout.app')
@section('content')
    <header id="header" class="ex-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Be right back.</h2>
                    <p>Be right back.</p>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </header> <!-- end of ex-header -->
@endsection
