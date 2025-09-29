{{ Form::open(['route' => ['biller.import.general', 'tasks'], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'files' => true, 'id' => 'import-data']) }}
    <input type="hidden" name="update" value="1">
    <input type="hidden" name="project_id" value="{{$data['project_id']}}">
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    {{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}