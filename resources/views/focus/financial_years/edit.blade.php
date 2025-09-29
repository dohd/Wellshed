{{--@extends('core.layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container">--}}
{{--        <h1>Edit Financial Year</h1>--}}
{{--        <form action="{{ route('biller.financial_years.update', $financialYear->id) }}" method="POST">--}}
{{--            @csrf--}}
{{--            @method('PUT')--}}
{{--            <div class="form-group">--}}
{{--                <label for="start_date">Start Date</label>--}}
{{--                <input type="date" name="start_date" class="form-control" value="{{ $financialYear->start_date }}" required>--}}
{{--            </div>--}}
{{--            <div class="form-group">--}}
{{--                <label for="end_date">End Date</label>--}}
{{--                <input type="date" name="end_date" class="form-control" value="{{ $financialYear->end_date }}" required>--}}
{{--            </div>--}}
{{--            <button type="submit" class="btn btn-primary">Update</button>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--@endsection--}}



@extends ('core.layouts.app')

@section ('title',  'Edit Financial Year')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">

            <div class="content-header-left col-6">
                <h3 class="mb-0">Edit {{ $financialYear->name }}</h3>
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

{{--                                    <form action="{{ route('biller.financial_years.update', $financialYear->id) }}" method="GET">--}}

                                    {{ Form::open(['route' => ['biller.financial_years.update', $financialYear->id], 'method' => 'PUT', 'id' => 'edit-financial-year']) }}
                                        @csrf

                                        <div class="row">

                                            <div class="form-group col-12 col-lg-8">
                                                <label for="start_date">Start Date</label>
                                                <input type="date" name="start_date" class="form-control" value="{{ $financialYear->start_date }}" required>
                                            </div>
                                            <div class="form-group col-12 col-lg-8">
                                                <label for="end_date">End Date</label>
                                                <input type="date" name="end_date" class="form-control" value="{{ $financialYear->end_date }}" required>
                                            </div>

                                        </div>

                                        <button type="submit" class="btn btn-primary">Update</button>

                                    {{ Form::close() }}
{{--                                    </form>--}}

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
@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>



    </script>
@endsection