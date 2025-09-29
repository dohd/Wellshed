

<div class="modal fade" id="terminate" role="dialog" aria-labelledby="terminateLabel" aria-hidden="true">
    <div class="modal-dialog mw-50" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="terminateLabel">Terminate Contract</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            {{ Form::open(['route' => 'biller.salary.terminate_contract' ]) }}
            
            <div class='form-group row'>
                <div class='col-10'>
                    {{ Form::label( 'employee', 'Employee Name',['class' => 'control-label']) }}
                    <input type="text" name="employee_name"  value="{{ @$salary->employee_name?: 1 }}" class="form-control" id="employee" readonly>
                    <input type="hidden" name="employee_id" value="{{ @$salary->employee_id ?: 1 }}" id="employeeid">
                    <input type="hidden" name="employee_name" value="{{ @$salary->employee_name?: 1 }}" id="employee">
                    <input type="hidden" name="id" id="id" value="{{ @$salary->id?: 1 }}">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-10">
                    {{ Form::label('status', 'Status',['class' => 'control-label']) }}
                    <select class="form-control round" id="employeebox" name="status" data-placeholder="Search Employee">
                        <option value="terminated">Terminate</option>
                    </select>
                </div>
                
            </div>
            <div class="form-group row">
                <div class="col-10">
                    {{ Form::label( 'terminate_date', 'Terminate Date',['class' => 'control-label']) }}
                    {{ Form::date('terminate_date', null, ['class' => 'form-control round', 'placeholder' => '']) }}
                </div>
                
            </div>
            
            <div class="edit-form-btn float-right">
                {{-- {{ link_to_route('biller.queuerequisitions.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }} --}}
                {{ Form::submit( @$salary? 'Create' : 'Renew', ['class' => 'btn btn-primary btn-md']) }}                                            
            </div>     
            {{ Form::close() }}
            
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>