<div class="form-group ml-1">
    <label for="department"> Department </label>
    <select name="department" id="department" class="form-control col-12 col-lg-10">
        @foreach ($departments as $dept)
            <option value="{{ $dept->id }}" {{ $dept->id === @$jobtitles->department_id ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
        @endforeach
    </select>
</div>

<div class='form-group'>
    {{ Form::label( 'name','Job Title Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' =>'Job Title Name']) }}
    </div>
</div>

<div class="form-group col-12 col-lg-10">
    <label for="job_grade">Job Grade</label>
    <select name="job_grade" id="job_grade" class="form-control" required>
        @foreach ($jobGrades as $grade => $label)
            <option value="{{ $grade }}" {{ $grade === @$jobtitles->job_grade ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

<div class='form-group'>
    {{ Form::label( 'note', trans('departments.note'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('note', null, ['class' => 'form-control round', 'placeholder' => trans('departments.note')]) }}
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

// On searching supplier
$('#departmentbox').change(function() {
    const name = $('#departmentbox option:selected').text().split(' : ')[0];
    const [id, taxId] = $(this).val().split('-');
    $('#departmentid').val(id);
    $('#department').val(name);
});

        $('#job_grade').select2({allowClear: true});


// load departments
const departmentUrl = "{{ route('biller.jobtitles.select') }}";
function departmentData(data) {
    return {results: data.map(v => ({id: v.id, text: v.name}))};
}

$('#departmentbox').select2(select2Config(departmentUrl, departmentData));


// select2 config
function select2Config(url, callback) {
    return {
        ajax: {
            url,
            dataType: 'json',
            type: 'POST',
            quietMillis: 50,
            data: ({term}) => ({q: term, keyword: term}),
            processResults: callback
        }
    }
}
    </script>
@endsection
