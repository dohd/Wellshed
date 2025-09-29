{{ Form::open(['route' => ['biller.import.general', 'prospect'], 'method' => 'POST', 'files' => true, 'id' => 'import-data']) }}
    {{ Form::hidden('update', 1) }}
    {!! Form::file('import_file', array('class'=>'form-control input col-md-6 mb-1' )) !!}
    <div class="row form-group">
        
        <div class="col-4">
            <label for="title">Title</label>
            {{ Form::text('title', null, ['class' => 'form-control', 'required']) }}
        </div>
    </div>
    {{ Form::submit(trans('import.upload_import'), ['class' => 'btn btn-primary btn-md']) }}
{{ Form::close() }}
