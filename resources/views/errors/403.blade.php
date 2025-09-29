@section('title',company()->seo_title)
@section('content')
    <header id="header" class="ex-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>{{ $exception->getMessage() }}</h2>
                    <p>{{ $exception->getMessage() }}</p>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </header> <!-- end of ex-header -->
@endsection