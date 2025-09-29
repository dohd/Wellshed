
<div class="form-group row">
    <div class="col-lg-10">
        {{ Form::label( 'subject', 'Subject',['class' => 'control-label']) }}
        <textarea name="subject" id="subject" cols="35" rows="2" class="form-control" required>{{@$send_email->subject}}</textarea>
    </div>
    
</div>
<div class='form-group row'>
    <div class='col-lg-10'>
        {{ Form::label( 'text_email', 'Email Text',['class' => 'control-label']) }}<br/>
        <textarea name="text_email" id="text_email" cols="35" rows="3" class="form-control" required>{{@$send_email->text_email}}</textarea>
        <input type="hidden" id="company" name="company_name" value="{{$company_name}}">
    </div>
</div>
<div class='form-group row'>
    <div class='col-lg-3'>
        {{ Form::label( 'user_type', 'Type of User',['class' => 'control-label']) }}
        <select name="user_type" id="user_type" class="form-control" required>
            <option value="">Select Type of User</option>
            @foreach (['employee','customer','supplier','labourer','prospect'] as $item)
                <option value="{{$item}}">{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <label for="date">Delivery Type</label>
        <select name="delivery_type" id="delivery_type" class="form-control">
            {{-- <option value="">Select Delivery Type</option> --}}
            <option value="now" {{@$send_email->delivery_type == 'now' ? 'selected' : ''}}>Send Email Now</option>
            <option value="schedule" {{@$send_email->delivery_type == 'schedule' ? 'selected' : ''}}>Schedule For Later Date</option>
        </select>
    </div>
    <div class="col-lg-4">
        <label for="date">Schedule Date</label>
        <input type="datetime-local" name="scheduled_date" id="schedule_date"  class="form-control datepicker" disabled>
    </div>
</div>
<div class="form-group row div_employee d-none">
    <div class="col-lg-10">
        <label for="">Employees</label><button type="button" id="reset-employee" class="btn btn-sm btn-success ml-4"><i class="fa fa-refresh" aria-hidden="true"></i></button>
        <select name="employee_id[]" id="employee" class="form-control" data-placeholder="Select Employee" aria-rowspan="3" multiple>
            @foreach ($employees as $employee)
                <option value="{{$employee->id}}" selected>{{$employee->fullname}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row div_customer d-none">
    <div class="col-lg-10">
        <label for="">Customers</label><button type="button" id="reset-customer" class="btn btn-sm btn-success ml-4"><i class="fa fa-refresh" aria-hidden="true"></i></button>
        <select name="customer_id[]" id="customer" class="form-control" data-placeholder="Select Customer" aria-rowspan="3" multiple>
            @foreach ($customers as $customer)
                <option value="{{$customer->id}}" selected>{{$customer->company ?: $customer->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row div_supplier d-none">
    <div class="col-lg-10">
        <label for="">Suppliers</label><button type="button" id="reset-supplier" class="btn btn-sm btn-success ml-4"><i class="fa fa-refresh" aria-hidden="true"></i></button>
        <select name="supplier_id[]" id="supplier" class="form-control" data-placeholder="Select Supplier" aria-rowspan="3" multiple>
            @foreach ($suppliers as $supplier)
                <option value="{{$supplier->id}}" selected>{{$supplier->company ?: $supplier->name}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row div_labourer d-none">
    <div class="col-lg-10 mt-2 mb-3">
        <label for="">Projects</label>
        <select id="project" class="form-control" data-placeholder="Select Project" aria-rowspan="3">
            <option value="">Select Project</option>
            @foreach ($projects as $project)
                <option value="{{$project['id']}}">{{$project['name']}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-10">
        <label for="">Labourers</label><button type="button" id="reset-labourer" class="btn btn-sm btn-success ml-4"><i class="fa fa-refresh" aria-hidden="true"></i><span>Clear</span></button>
        <select name="labourer_id[]" id="labourer" class="form-control" data-placeholder="Select Labourer" aria-rowspan="3" multiple>
            @foreach ($labourers as $labourer)
                <option value="{{$labourer->id}}" selected>{{$labourer->company ?: $labourer->name}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row div_prospect d-none">
    <div class="col-lg-10 mt-2 mb-3">
        <label for="">Industry</label>
        <select id="prospect_industry" class="form-control" data-placeholder="Select Prospect Industries" aria-rowspan="3">
            <option value="">Select Prospect Industries</option>
            @foreach ($prospect_industries as $industry)
                <option value="{{$industry}}">{{$industry}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-10">
        <label for="">Prospects</label><button type="button" id="reset-prospect" class="btn btn-sm btn-success ml-4"><i class="fa fa-refresh" aria-hidden="true"></i><span>Clear</span></button>
        <select name="prospect_id[]" id="prospect" class="form-control" data-placeholder="Select Prospect" aria-rowspan="3" multiple>
            @foreach ($prospects as $prospect)
                <option value="{{$prospect->id}}" selected>{{$prospect->company ?: $prospect->contact_person}}</option>
            @endforeach
        </select>
    </div>
</div>