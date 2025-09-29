<!DOCTYPE html>
@include('tinymce.scripts')

@extends('core.layouts.app')

@section('title', 'Employee Notice')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Employee Notice</h4>

            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">

                    <div class="media-body media-right text-right">
                        @include('focus.employeeNotice.partials.header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="card">

            <div class="card-body">

                <div>



                    <div class="row mb-2">
                        <div class="col-12 col-lg-8">
                            <label for="name" class="mt-2">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control" required @if(@$employeeNotice) disabled @endif>
                                <option value=""> Select Employee </option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp['id'] }}" @if(@$employeeNotice->employee_id === $emp['id']) selected @endif>{{ $emp['first_name'] . " " . $emp['last_name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-lg-8">
                            <label for="title" class="mt-2">Title</label>
                            <input type="text" id="title" name="title" required class="form-control box-size" placeholder="Set a suitable title" @if(@$employeeNotice) value="{{@$employeeNotice->title}} @endif" disabled>
                        </div>

                        <div class="col-12 col-lg-8 row">

                            <div class="col-12 col-lg-6">
                                <label for="date" class="mt-2">Date</label>
                                <input type="date" id="date" name="date" class="form-control box-size" required @if(@$employeeNotice) value="{{ @$employeeNotice->date }}" @endif disabled>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label for="file" class="mt-2">Document</label>
                                @if(@$employeeNotice->document_path)
                                    <div>
                                        <a href="{{ route('biller.employee-notice.download', @$employeeNotice->id) }}" class="btn btn-success">Download Current File</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="content" class="mt-2">Content</label>
                            <textarea type="content" id="content" name="content" class="form-control box-size tiny"> @if(@$employeeNotice) {{@$employeeNotice->content}} @endif</textarea>
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
        $(document).ready(function () {

            $('#employee_id').select2({allowClear: true});

            $(() => {
                tinymce.init({
                    selector: '.tiny',
                    menubar: '',
                    plugins: '',
                    toolbar: '',
                    height: 400,
                    readonly  : true,
                });
            });


        });
    </script>
@endsection
