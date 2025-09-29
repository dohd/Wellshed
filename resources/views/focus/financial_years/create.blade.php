@extends ('core.layouts.app')

@section ('title',  'Create Financial Year')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">

            <div class="content-header-left col-6">
                <h3 class="mb-0">Create Financial Year</h3>
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

                                    <form action="{{ route('biller.financial_years.store') }}" method="POST">
                                        @csrf

                                        <div class="row">

                                            <div class="form-group col-12 col-lg-8">
                                                <label for="start_date">Start Date</label>
                                                <input type="date" name="start_date" class="form-control" required>
                                            </div>
                                            <div class="form-group col-12 col-lg-8">
                                                <label for="end_date">End Date</label>
                                                <input type="date" name="end_date" class="form-control" required>
                                            </div>

                                        </div>

                                        <button type="submit" class="btn btn-primary">Save</button>

                                    </form>

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