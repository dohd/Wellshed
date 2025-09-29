@extends ('core.layouts.app')

@section ('title', 'Salary Management'.'|'. 'Create')

@section('page-header')
    <h1>
        {{ 'Salary Management'}}
        <small>{{ 'Create' }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create Salary</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.salary.partials.salary-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    {{ Form::open(['route' => 'biller.salary.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.salary.form")
                                        
                                      
                                    </div><!-- form-group -->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
        
// On searching supplier
$('#employeebox').change(function() {
    const name = $('#employeebox option:selected').text().split(' : ')[0];
    const [id, taxId] = $(this).val().split('-');
    $('#employeeid').val(id);
    $('#employee').val(name);
});


// load employees
const employeeUrl = "{{ route('biller.salary.select') }}";
function employeeData(data) {
    return {results: data.map(v => ({id: v.id, text: v.employee_no + ' - ' + v.first_name + ' ' + v.last_name}))};
}
$('#employeebox').select2(select2Config(employeeUrl, employeeData));
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

let tableRow = $('#productsTbl tbody tr:first').html();
    $('#productsTbl tbody tr:first').remove();
    let rowIds = 1;
    $('#addstock').click(function() {
        rowIds++;
        let i = rowIds;
        const html = tableRow.replace(/-0/g, '-'+i);
        $('#productsTbl tbody').append('<tr>' + html + '</tr>');
        $('#productsTbl').on('change','.deduct', deduct);
    });
    $('#productsTbl').on('click', '.remove', removeRow);
    function removeRow() {
        const $tr = $(this).parents('tr:first');
        $tr.remove();
    }
    let rowId = 0;
    
    function deduct() {
        const name = $('#deductname option:selected').val();
        let i = rowId;
        if (name == "NHIF") {
            $('#deduction_id-'+i).val('1').change();
        }
        else if (name == 'NSSF') {
            $('#deduction_id-'+i).val('2').change();
        } else {
            $('#deduction_id-'+i).val('3').change();
        }
        
    }
    </script>
@endsection