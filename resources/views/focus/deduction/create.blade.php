@extends ('core.layouts.app')

@section ('title', 'Deductions Management'.'|'. 'Create')

@section('page-header')
    <h1>
        {{ 'Deductions Management'}}
        <small>{{ 'Create' }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Create</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.deduction.partials.deductions-header-buttons')
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
                                    {{ Form::open(['route' => 'biller.deductions.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'create-department']) }}


                                    <div class="form-group mt-2">
                                        {{-- Including Form blade file --}}
                                        @include("focus.deduction.form")
                                        <div class="edit-form-btn float-right mt-5">
                                            {{ link_to_route('biller.deductions.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
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
@section('extra-scripts')
<script>
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
        $tr.next().remove();
        $tr.remove();
    }
    let rowId = 0;
    
    function deduct() {
        const name = $('#deductname option:selected').val();
        let i = rowId;
        if (name == "NHIF") {
            $('#deduction_id-'+i).val('1').change();
            console.log(name);
        }
        else if (name == 'NSSF') {
            $('#deduction_id-'+i).val('2').change();
        } else {
            $('#deduction_id-'+i).val('3').change();
        }
        
    }
</script>
@endsection