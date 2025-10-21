<div class="form-group">
    {{ Form::label('customer', 'Customer',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-6'>
        <select class="form-control col-lg-10" name="customer_id" id="customer" data-placeholder="Choose Customer" required>
            <option></option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ @$subscription->customer_id === $customer->id ? 'selected' : '' }}>
                    {{ $customer->company && $customer->name? "{$customer->company}: {$customer->name}" : ($customer->company ?? $customer->name) }} 
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    {{ Form::label('package', 'Subscription Package',['class' => 'col-lg-2 control-label']) }}
    <div class='col-lg-6'>
        <select class="form-control col-lg-10" name="sub_package_id" id="subpackage" data-placeholder="Choose Customer" required>
            <option></option>
            @foreach($subpackages as $subpackage)
                <option value="{{ $subpackage->id }}" {{ @$subscription->sub_package_id === $subpackage->id ? 'selected' : '' }}>
                    {{ gen4tid('PKG-', $subpackage->tid) }} - {{ $subpackage->name }} 
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class='form-group'>
    {{ Form::label('start', 'Start Date', ['class' => 'col-lg-2 control-label']) }}
    <div class="col-lg-6">
        {{ Form::datetimeLocal('start_date', null, ['class' => 'form-control box-size', 'required' => 'required']) }}
    </div>
</div>
<div class='form-group'>
    {{ Form::label('end', 'End Date', ['class' => 'col-lg-2 control-label']) }}
    <div class="col-lg-6">
        {{ Form::datetimeLocal('end_date', null, ['class' => 'form-control box-size', 'required' => 'required']) }}
    </div>
</div>


@section("after-scripts")
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        
    }

    const Form = {
        init() {
           $('#customer, #subpackage').select2({allowClear: true});         
        },
    }

    $(Form.init);
</script>
@endsection