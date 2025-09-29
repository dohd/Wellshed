<div class="row">
    <div class="col-12">
        <label for="date">Objective</label>
        {{-- <input type="text" id="objective" name="name" required class="datepicker form-control box-size mb-2"> --}}
        {{ Form::text('name', null, ['class' => 'form-control mb-2', 'id' => 'date_of_request', 'required'=>'required']) }}
    </div>
</div>

