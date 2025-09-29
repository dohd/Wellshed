
<div class='form-group'>
    {{ Form::label( 'name','Fault Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' =>'Fault Name']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'notes', trans('departments.note'),['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('notes', null, ['class' => 'form-control round', 'placeholder' => trans('departments.note')]) }}
    </div>
</div>

@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
    <script type="text/javascript">
        $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});

    </script>
@endsection
