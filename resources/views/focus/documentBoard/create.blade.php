@extends ('core.layouts.app')

@section ('title', "Company Notice Board")

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h2 class=" mb-0">Upload to Company Notice Board</h2>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.documentBoard.header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">

                <div class="col-12">

                    <div class="card p-2" style="border-radius: 8px;">


                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('biller.company-notice-board.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col-12 col-lg-8">
                                    <label for="caption">Caption</label>
                                    <input type="text" name="caption" id="caption" class="form-control" required>
                                </div>
                                <div class="form-group col-12 col-lg-8">
                                    <label for="file">File</label>
                                    <input type="file" name="file" id="file" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Upload</button>
                        </form>

                    </div>

                </div>

            </div>
        </div>

    </div>
@endsection























@section('content')
    <div class="container">
        <h1>Upload Document</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('biller.company-notice-board.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="caption">Caption</label>
                <input type="text" name="caption" id="caption" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="file">File</label>
                <input type="file" name="file" id="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Upload</button>
        </form>
    </div>
@endsection
