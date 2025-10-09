@extends ('core.layouts.app')

@section ('title', 'Edit Target Zones')

@section('page-header')
    <h1>
        <small>Edit Target Zones</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Target Zones</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.target_zones.partials.target_zones-header-buttons')
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
                                    {{ Form::model($target_zone, ['route' => ['biller.target_zones.update', $target_zone], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.target_zones.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.target_zones.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
        };


        const Index = {
            init() {
                $.ajaxSetup(config.ajax);
                let docRowId = $("#daysTbl tbody tr").length;
                const docRow = $('#daysTbl tbody tr').html();
                $('#daysTbl tbody tr:first').remove(); 
                $('#addDoc').click(function() {
                    docRowId++;
                    let html = docRow.replace(/-0/g, '-'+docRowId);
                    $('#daysTbl tbody').append('<tr>' + html + '</tr>');
                });
                // remove schedule row
                $('#daysTbl').on('click', '.remove', function() {
                    $(this).parents('tr').remove();
                    docRowId--;
                });
            },
        };

        $(() => Index.init());
    </script>
@endsection