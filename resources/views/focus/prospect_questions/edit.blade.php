@extends ('core.layouts.app')

@section ('title',  'Edit Prospect Question')

@section('page-header')
    <h1>
        
        <small>{{ 'Edit Prospect Question' }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Edit Prospect Question' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.prospect_questions.partials.prospect_questions-header-buttons')
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
                                    {{ Form::model($prospect_questions, ['route' => ['biller.prospect_questions.update', $prospect_questions], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.prospect_questions.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.prospect_questions.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->

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
        const rowHtml = $("#questionRow").html();
        $("#questionRow").remove();
        let rowId = $("#questionTbl tbody tr").length;
        $('#addRow').click(function() {

            const i = 'p' + rowId;
            console.log(rowHtml, i);
            const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
            $("#questionTbl tbody").append(newRowHtml);
            // $('#increment-'+i).text(i+1);
            rowId++;
        });
        $("#questionTbl").on("click", ".remove", function() {
            const menu = $(this);
            const row = $(this).parents("tr:first");
            if (menu.is('.remove') && confirm('Are you sure?')) {
                row.remove();
            }
        });
    </script>
@endsection