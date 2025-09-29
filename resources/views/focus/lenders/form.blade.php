<div class='form-group'>
    {{ Form::label( 'name', 'Full Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Full Name','required'=>'']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'bank', 'Contact',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('contact', null, ['class' => 'form-control round', 'placeholder' => 'Contact','required'=>'']) }}
    </div>
</div>


@section("after-scripts")
    <script type="text/javascript">
        //Put your javascript needs in here.
        //Don't forget to put `@`parent exactly after `@`section("after-scripts"),
        //if your create or edit blade contains javascript of its own
        $(document).ready(function () {
            //Everything in here would execute after the DOM is ready to manipulated.
        });
    </script>
@endsection
