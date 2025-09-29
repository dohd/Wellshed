<!DOCTYPE html>

@extends ('core.layouts.app')

<head>
    <script src="https://cdn.tiny.cloud/1/ewcb9ttdxkr6mv3uyc8ueykuqz06aja4t3e7wuqyfqfwq17z/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>


@section ('title',  'Job Grades')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h3 class="mb-0">Job Grade Salary Ranges</h3>
        </div>

    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">
                            {{ Form::open(['route' => 'biller.job-grades.store', 'method' => 'POST', 'id' => 'create-job-grades']) }}
                            <div class="form-group">
                                {{-- Including Form blade file --}}
                                @include("focus.jobGrade.form")
                                <div class="edit-form-btn">
                                    {{ link_to_route('biller.job-grades.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-2']) }}
                                    {{ Form::submit('Set Job Grade Salary Ranges', ['class' => 'btn btn-primary btn-md']) }}
                                    <div class="clearfix"></div>
                                </div>                                    
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
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#purchase_date').datepicker('setDate', new Date());
    $('#warranty_expiry_date').datepicker('setDate', new Date());


    tinymce.init({
        selector: '.tinyinput',
        menubar: false,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
        height: 300,
    });

</script>
@endsection