<div class="form-group row ml-2">
    <div class="col-md-8">
        <label for="name" class="caption">Name</label>
        <div class="input-group">
            <div class="w-100">
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
    </div>
</div>

@section('extra-scripts')
<script type="text/javascript">

</script>
@endsection