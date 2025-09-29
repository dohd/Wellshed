@extends ('core.layouts.app')

@section ('title', 'Edit Tender')

@section('page-header')
    <h1>
       Manage Tenders
        <small>Edit Tender</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit Tender</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.tenders.partials.tenders-header-buttons')
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
                                    {{ Form::model($tender, ['route' => ['biller.tenders.update', $tender], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}
                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.tenders.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.tenders.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
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
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        };

        const Index = {
            init(){
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
                $('#team_member_ids, #lead_id').select2({allowClear: true});

                $('#lead_id').change(function() {
                    let text = $(this).find(':selected').attr('title') || '';
                    text = text.replace(/\s+/g, ' ').trim();
                    $('#title').val(text);
                });

                const tender =  @json($tender);
                if(tender){
                    $('#date').datepicker('setDate', new Date(tender.date));
                    $('#submission_date').datepicker('setDate', new Date(tender.submission_date));
                    $('#site_visit_date').datepicker('setDate', new Date(tender.site_visit_date));
                }
            }
        };
        $(Index.init);
    </script>
@endsection
