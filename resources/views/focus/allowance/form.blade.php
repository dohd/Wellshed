<div class='form-group'>
    {{ Form::label( 'name', 'Name',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Name','required'=>'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label( 'type', 'Type',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        
      
            {!! Form::select('type', ['Allowance'=>'Allowance','Deductions'=>'Deductions'], null, [
                'placeholder' => '-- Select Type --',
                'class' => ' form-control round',
                'id' => 'type',
                'required' => 'required',
            ]) !!}
            </div>
  
</div>
<div class='form-group'>
    {{ Form::label( 'is_taxable', 'Is Taxable',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-10'>
        
      
            {!! Form::select('is_taxable', ['No'=>'No','Yes'=>'Yes'], null, [
                'placeholder' => '-- Select Type --',
                'class' => ' form-control round',
                'id' => 'is_taxable',
                'required' => 'required',
            ]) !!}
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
