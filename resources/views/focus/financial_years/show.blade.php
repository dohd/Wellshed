@extends('core.layouts.app')

@section('content')
    <div class="container">
        <h1>Financial Year Details</h1>
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="text" class="form-control" value="{{ $financialYear->start_date }}" readonly>
        </div>
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="text" class="form-control" value="{{ $financialYear->end_date }}" readonly>
        </div>
        <div class="form-group">
            <label for="ins">Created By</label>
            <input type="text" class="form-control" value="{{ $financialYear->ins }}" readonly>
        </div>
        <a href="{{ route('biller.financial_years.index') }}" class="btn btn-primary">Back</a>
    </div>
@endsection
