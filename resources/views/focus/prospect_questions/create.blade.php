@extends ('core.layouts.app')

@section ('title',  'Create Prospect Question')

@section('page-header')
    <h1>
        <small>{{ 'Create Prospect Question' }}</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Create Prospect Question' }}</h4>

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
                                    {{ Form::open(['route' => 'biller.prospect_questions.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post']) }}


                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.prospect_questions.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.prospect_questions.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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

        let rowId = 1;
        $('#increment-0').text(1);
        const rowHtml = $("#questionRow").html();
        $('#addRow').click(function() {
            const i = 'p' + rowId;
            const newRowHtml = '<tr>' + rowHtml.replace(/p0/g, i) + '</tr>';
            $("#questionTbl tbody").append(newRowHtml);
            let row = $("#questionTbl tbody tr:last");
            $('#increment-p'+i).text(i+1);
            rowId++;
        });
        // On clicking action drop down
        $("#questionTbl").on("click", ".remove", function() {
            const menu = $(this);
            const row = $(this).parents("tr:first");
            if (menu.is('.remove') && confirm('Are you sure?')) {
                row.remove();
            }
        });
    </script>
@endsection