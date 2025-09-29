@extends('core.layouts.app')

@section('title', 'Edit | Budget Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="alert alert-warning col-12 d-none budget-alert" role="alert">
            <strong>E.P Margin Not Met!</strong> Check line item rates.
        </div>
    </div>

    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Budget Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">              
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">                
                {{ Form::model($labour_item, ['route' => ['biller.labour_allocations.update_item', $labour_item], 'method' => 'PATCH']) }}
                <div class="form-group">
                    <div class="col mt-2">
                        <label for="date">Date</label>
                        {{-- <input type="date" class="form-control" name="date" id="date" >
                        <input type="hidden" value="{{$id}}" readonly name="labour_id" id=""> --}}
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' =>'labour_date']) }}
                        {{ Form::hidden('labour_id', null, ['class' => 'form-control']) }}
                    </div>
                    <div class="col mt-2">
                        <label for="hrs">Hours</label>
                        {{ Form::number('hrs', null, ['class' => 'form-control', 'required']) }}
                    </div>
                    <div class="col mt-2">
                        <label for="type">Type of Work Done</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">-----Select Type -------</option>
                            <option value="repair" {{ $labour_item->type == 'repair' ? 'selected': ''}}>Repair</option>
                            <option value="maintenance" {{ $labour_item->type == 'maintenance' ? 'selected': ''}}>Maintenance</option>
                            <option value="installation" {{ $labour_item->type == 'installation' ? 'selected': ''}}>Installation</option>
                            <option value="others" {{ $labour_item->type == 'others' ? 'selected': ''}}>Others</option>
                        </select>
                    </div>
                   </div>
                   {{ Form::submit('Update', ['class' => 'btn btn-success btn-lg']) }}
                {{ Form::close() }}
            </div>             
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
    <script>
        // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#labour_date').datepicker('setDate', new Date());
    $('#date').datepicker('setDate', new Date());

    </script>
@endsection
