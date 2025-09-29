

<div class="modal fade" id="renew" role="dialog" aria-labelledby="renewLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="renewLabel">Renew Contract</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            {{ Form::open(['route' => 'biller.salary.renew_contract' ]) }}
            
            <div class='form-group row'>
                <div class='col-4'>
                    {{ Form::label( 'employee', 'Employee Name',['class' => 'control-label']) }}
                    <input type="text" name="employee_name" value="{{ @$salary->employee_name?: 1 }}" disabled class="form-control" id="employee">
                    <input type="hidden" name="employee_id" value="{{ @$salary->employee_id ?: 1 }}" id="employeeid">
                    <input type="hidden" name="employee_name" value="{{ @$salary->employee_name?: 1 }}" id="employee">
                    <input type="hidden" name="id" id="id" value="{{ @$salary->id?: 1 }}">
                </div>
                <div class='col-4'>
                    {{ Form::label( 'basic_pay', 'Basic Pay',['class' => 'control-label']) }}
                    {{ Form::number('basic_pay',  @$salary->basic_pay, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
                </div>
                <div class='col-4'>
                    {{ Form::label( 'house_allowance', 'House Allowance',['class' => 'control-label']) }}
                    {{ Form::number('house_allowance',  @$salary->house_allowance, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
                </div>
            </div>
            <div class='form-group row'>
                <div class='col-4'>
                    {{ Form::label( 'transport_allowance', 'Transport Allowance',['class' => 'control-label']) }}
                    {{ Form::number('transport_allowance',  @$salary->transport_allowance, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
                </div>
                <div class='col-4'>
                    {{ Form::label( 'directors_fee', 'Directors Fees',['class' => 'control-label']) }}
                    {{ Form::number('directors_fee',  @$salary->directors_fee, ['class' => 'form-control round', 'placeholder' => '0.00']) }}
                </div>
                <div class="col-4">
                    {{ Form::label( 'contract_type', 'Contract Type',['class' => 'control-label']) }}
                    <select class="form-control round" name="contract_type" id="employeebox" data-placeholder="Search Contract">
                        <option value="permanent">Permanent</option>
                        <option value="contract">Contract</option>
                        <option value="casual">Casual</option>
                        <option value="intern">Intern</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-4">
                    {{ Form::label('workshift', 'Select Workshift',['class' => 'control-label']) }}
                    <select class="form-control round" name="workshift_id"  data-placeholder="Search Workshift">
                        @foreach ($workshifts as $work)
                            <option value="{{$work->id}}" {{ $work->id == @$salary->workshift_id ? 'selected' : '' }}>{{$work->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    {{ Form::label( 'start_date', 'Start Date',['class' => 'control-label']) }}
                    {{ Form::date('start_date', null, ['class' => 'form-control round', 'placeholder' => '','required']) }}
                </div>
                <div class="col-4">
                    {{ Form::label( 'duration', 'Duration',['class' => 'control-label']) }}
                    {{ Form::number('duration', null, ['class' => 'form-control round', 'placeholder' => '1', 'required']) }}
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