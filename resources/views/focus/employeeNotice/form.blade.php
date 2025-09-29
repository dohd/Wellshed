<!DOCTYPE html>
@include('tinymce.scripts')


<div>

    <form

        action="{{ isset($employeeNotice) ? route('biller.employee-notice.update', @$employeeNotice->id) : route('biller.employee-notice.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @if(isset($employeeNotice))
            @method('PUT')
        @endif


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
                <input type="text" id="title" name="title" required class="form-control box-size" placeholder="Set a suitable title" @if(@$employeeNotice) value="{{@$employeeNotice->title}} @endif">
            </div>

            <div class="col-12 col-lg-8 row">

                <div class="col-12 col-lg-6">
                    <label for="date" class="mt-2">Date</label>
                    <input type="date" id="date" name="date" class="form-control box-size" required @if(@$employeeNotice) value="{{ @$employeeNotice->date }}" @endif>
                </div>

                <div class="col-12 col-lg-6">
                    <label for="file" class="mt-2">File</label>
                    <input type="file" name="file" id="file" class="form-control">
                    @if(@$employeeNotice->document_path)
                        <a href="{{ route('biller.employee-notice.download', @$employeeNotice->id) }}" class="btn btn-success">Download Current File</a>
                    @endif
                </div>
            </div>

            <div class="col-12">
                <label for="content" class="mt-2">Content</label>
                <textarea type="content" id="content" name="content" class="form-control box-size tiny"> @if(@$employeeNotice) {{@$employeeNotice->content}} @endif</textarea>
            </div>

    </div>

        <button type="submit" class="btn btn-primary mt-3">{{ isset($employeeNotice) ? 'Update Notice' : 'Save Notice'}}</button>

    </form>

@section('extra-scripts')
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        $(document).ready(function () {

            $('#employee_id').select2({allowClear: true});

            $(() => {
                tinymce.init({
                    selector: '.tiny',
                    menubar: false,
                    plugins: 'anchor autolink charmap codesample image link lists media searchreplace table visualblocks wordcount',
                    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
                    height: 400,
                    license_key: 'gpl'
                });
            });


        });
    </script>
@endsection
