<div class="row">
    <div class="col-md-4 pr-0">
        <div class='form-group'>
            {{ Form::label('customer', 'Customer', ['class' => 'col control-label']) }}
            <div class='col-12'>
                <select name="customer_id" id="customer"  data-placeholder="Search Customer">
                    @if (isset($client_user->customer))
                        <option selected value="{{ @$client_user->customer_id }}">{{ $client_user->customer->company }}</option>
                    @endif
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-4 pl-0 pr-0">
        <div class='form-group'>
            {{ Form::label('branch', 'Branch', ['class' => 'col control-label']) }}
            <div class='col-12'>
                <select name="branch_id" id="branch"  data-placeholder="Search Branch">
                    @if (isset($client_user->customer))
                        <option selected value="{{ @$client_user->branch_id }}">{{ $client_user->branch->name }}</option>
                    @endif
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-4 pl-0 pr-0">
        <div class='form-group'>
            {{ Form::label('location', 'Location',['class' => 'col-12 control-label']) }}
            <div class='col-12'>
                {{ Form::text('location', null, ['class' => 'form-control box-size', 'placeholder' => 'Location']) }}
            </div>
        </div>
    </div>
</div>

<hr>
{{-- User Info --}}
<h6 class="mb-2">User Info</h6>
<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <div class='form-group'>
                    {{ Form::label('first_name', 'First Name',['class' => 'col-12 control-label']) }}
                    <div class='col-12'>
                        {{ Form::text('first_name', @$client_user->user->first_name, ['class' => 'form-control box-size', 'placeholder' => 'First Name']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class='form-group'>
                    {{ Form::label('last_name', 'Last Name',['class' => 'col-12 control-label']) }}
                    <div class='col-12'>
                        {{ Form::text('last_name', @$client_user->user->last_name, ['class' => 'form-control box-size', 'placeholder' => 'Last Name']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class='form-group'>
                    {{ Form::label('email', 'Email', ['class' => 'col-12 control-label']) }}
                    <div class='col-12'>
                        {{ Form::text('email', @$client_user->user->email, ['class' => 'form-control box-size', 'placeholder' => 'Email']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="row">
            <div class="col-md-12">
                <div class='form-group'>
                    {{ Form::label( 'password', trans('customers.password'),['class' => 'col-12 control-label']) }}
                    <div class='col-12'>
                        {{ Form::password('password', ['class' => 'form-control box-size', 'id' => 'password']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class='form-group'>
                    {{ Form::label('password_confirmation', 'Confirm Password',['class' => 'col-12 control-label']) }}
                    <div class='col-12'>
                        {{ Form::password('password_confirmation', ['class' => 'form-control box-size', 'id' => 'confirm_password']) }}
                        <label for="password_match" class="text-danger d-none">Password does not match !</label>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="ml-2 password-condition">
                    <h4>Password must have:</h4>
                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least 7 Characters</h5>
                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> Contain Upper and Lowercase letters</h5>
                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one number</h5>
                    <h5 class="text-danger"><i class="fa fa-check" aria-hidden="true"></i> At least one symbol</h5>
                </div>
            </div>
        </div>
    </div>
</div>  

@section("after-scripts")
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        customerSelect2: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.tenants.customers') }}",
                dataType: 'json',
                method: 'POST',
                data: ({term}) => ({q: term}),
                processResults: data => {
                    return {
                        results: data.map(v => ({
                            id: v.id,
                            text: v.company, 
                        }))
                    }
                },
            },
        },
        branchSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.branches.select') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { results: data.map(v => ({text: v.name, id: v.id})) }
                },
            }
        },
    };

    $.ajaxSetup(config.ajax);
    $('#customer').select2(config.customerSelect2);
    $('#branch').select2(config.branchSelect);


    // password validation
    $('#password').on('keyup', function() {
        const div = $('.password-condition');
        const value = $(this).val();
        if (value.length >= 7) div.find('h5:first').removeClass('text-danger').addClass('text-success');
        else div.find('h5:first').removeClass('text-success').addClass('text-danger');
        if (new RegExp("[a-z][A-Z]|[A-Z][a-z]").test(value)) div.find('h5:eq(1)').removeClass('text-danger').addClass('text-success');
        else div.find('h5:eq(1)').removeClass('text-success').addClass('text-danger');
        if (new RegExp("[0-9]").test(value)) div.find('h5:eq(-2)').removeClass('text-danger').addClass('text-success');
        else div.find('h5:eq(-2)').removeClass('text-success').addClass('text-danger');
        if (new RegExp("[^A-Za-z 0-9]").test(value)) div.find('h5:last').removeClass('text-danger').addClass('text-success');
        else div.find('h5:last').removeClass('text-success').addClass('text-danger');
    });
    $('#confirm_password').on('keyup', function() {
        if ($(this).val() != $('#password').val()) $(this).next().removeClass('d-none');
        else $(this).next().addClass('d-none');
    });
</script>
@endsection   