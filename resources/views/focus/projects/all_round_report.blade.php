@extends ('core.layouts.app')

@section ('title', 'Project Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Project Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.projects.partials.projects-header-buttons')
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
                            {{ Form::open(['route' => array('biller.projects.get_all_report'), 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post','files' => false, 'id' => 'statement']) }}


                                <div class="form-group row">

                                    <label class="col-sm-3 control-label"
                                        for="sdate">Customer</label>

                                    <div class="col-sm-3">
                                        <select name="customer_id" id="customer" class="form-control" data-placeholder="Choose Customer">
                                            <option value=""></option>
                                            @foreach ($customers as $customer)
                                                <option value="{{$customer->id}}">{{$customer->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">

                                    <label class="col-sm-3 control-label"
                                        for="edate">Project</label>

                                    <div class="col-sm-3">
                                        <select name="project_id" id="projectFilter" class="form-control" data-placeholder="Choose Project">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                               
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label" for="pay_cat"></label>

                                    <div class="col-sm-4">
                                        <input type="submit" class="btn btn-primary btn-md" value="View">


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
@section('after-scripts')
{{-- For DataTables --}}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        projectSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.projects.project_load_select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        }
    };


    const Index = {
        startDate: '',
        endDate: '',
        
        init() {
            $.ajaxSetup(config.ajax);
           
            $("#customer").select2({allowClear: true}).change(Index.onChangeCustomer);
            $("#projectFilter").select2(config.projectSelect).change();
            
        },
        onChangeCustomer() {
            $("#projectFilter option:not(:eq(0))").remove();
        }
    };

    $(Index.init);
</script>
@endsection