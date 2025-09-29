<div class="card-content">
    <div class="card-body">        
        <div class="row">
            <!-- Casual Data -->
            <div class="col-md-6">
                <fieldset class="border p-1 mb-2">
                    <legend class="w-auto float-none h5">Personal Data</legend>
                    <div class='form-group'>
                        {{ Form::label( 'name', 'Full Name*', ['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('name', null, ['class' => 'form-control round', 'placeholder' => 'Full Name', 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label( 'id_number', 'ID Number*',['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('id_number', null, ['class' => 'form-control round', 'placeholder' => 'ID Number','required'=>'required' ]) }}
                        </div>
                    </div>
                    <div class='form-group row'>                        
                        <div class="col-md-6">
                            {{ Form::label( 'phone_number', trans('hrms.phone') . ' (Mobile-Money)*', ['class' => 'col-lg-6 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('phone_number', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.phone') . ' 1', 'required'=>'required']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            {{ Form::label( 'phone_number', 'Alternative Phone', ['class' => 'col-lg-6 control-label']) }}
                            <div class='col-lg-12'>
                                {{ Form::text('alt_phone_number', null, ['class' => 'form-control round', 'placeholder' => 'Phone 2']) }}
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label( 'gender', 'Gender*',['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {!! Form::select('gender', ['male'=>'Male', 'female'=>'Female', 'unspecified'=>'Unspecified'], null, [
                                'placeholder' => '-- Select Gender --',
                                'class' => 'custom-select round',
                                'id' => 'gender',
                                'required' => 'required',
                            ]) !!}
                        </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label( 'home_county', 'Home County',['class' => 'col-lg-2 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('home_county', null, ['class' => 'form-control round', 'placeholder' => 'Home County']) }}
                        </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label( 'home_address', 'Current Physical Residential area',['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('home_address', null, ['class' => 'form-control round', 'placeholder' => 'Current Physical Residential area']) }}
                        </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label( 'casual_description', 'Description',['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::textarea('casual_description', null, ['rows' => '1', 'class' => 'form-control', 'placeholder' => 'Casual Description']) }}
                        </div>
                    </div>
                    @include('focus.casuals.partials.casual_docs')
                    <div class='col-lg-12'>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="hasNextKin"> 
                            <label class="form-check-label" for="hasJobcard">Has Next of Kin?</label>
                        </div> 
                    </div>
                </fieldset>

                <!-- Next of Kin -->
                <fieldset class="border p-1 mb-2 next-kin-ctn d-none">
                    <legend class="w-auto float-none h5">Next of Kin</legend>
                    <div class='form-group'>
                        {{ Form::label('kin_relationship', 'Relationship', ['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {!! Form::select('kin_relationship', ['Wife'=>'Wife','Husband'=>'Husband','Father'=>'Father','Mother'=>'Mother','Brother'=>'Brother','Sister'=>'Sister', 'Son' => 'Son', 'Daughter' => 'Daughter'], null, [
                                'placeholder' => '-- Select Relationship --',
                                'class' => 'custom-select round',
                                'id' => 'kin_relationship',
                            ]) !!}
                        </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label( 'kin_name', 'Name',['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('kin_name', null, ['class' => 'form-control round', 'placeholder' => 'Name']) }}
                        </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label( 'kin_contact', 'Contact',['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('kin_contact', null, ['class' => 'form-control round', 'placeholder' => 'Phone']) }}
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Pay Set-up -->
            <div class="col-md-6">
                <fieldset class="border p-1 mb-2">
                    <legend class="w-auto float-none h5">Pay Set-up</legend>
                    <div class='form-group'>
                        {{ Form::label( 'job_category', 'Job Category*', ['class' => 'col-lg-12 control-label']) }}
                        <div class='col-lg-12'>
                            <select name="job_category_id" id="job_category" class="form-control" data-placeholder="Search Job Category">
                                <option value=""></option>
                                @foreach ($job_categories as $row)
                                    <option 
                                        value="{{ $row->id }}" 
                                        data-rate="{{ $row->rate }}" 
                                        {{ $row->id == @$casual->job_category_id ? 'selected' : '' }}
                                    >
                                    {{ $row->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>        
                    <div class='form-group row no-gutters'>
                            <div class="col-md-5">
                                {{ Form::label('rate', 'Hourly Pay Rate*', ['class' => 'col-lg-12 control-label']) }}
                                <div class='col-lg-12'>
                                    {{ Form::text('rate', null, ['class' => 'form-control round', 'id' => 'rate', 'placeholder' => '0.00']) }}
                                </div>
                            </div>
                            <div class="col-md-5">
                                {{ Form::label( 'Work Type', 'Work Type*',['class' => 'col-lg-12 control-label']) }}
                                <div class='col-lg-12'>
                                    {!! Form::select('work_type', ['contract' => 'Contract', 'non_contract' => 'Non-Contract','piecework' => 'PieceWork','casual_labourer'=>'Casual Labourer'], null, [
                                        'placeholder' => '-- Select Work Type --',
                                        'class' => 'custom-select round',
                                        'id' => 'WorkType',
                                        'required' => 'required',
                                    ]) !!}
                                </div>
                            </div>
                    </div>
                    <div class='form-group'>
                        {{ Form::label('wageItem', 'Wage Items', ['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            <select name="wage_item_id[]" id="wageItem" class="custom-select round" data-placeholder="Search Wage Items" multiple>
                                @foreach ($wageItems as $key => $value)
                                    <option value="{{ $key }}" {{ @$wageItemIds && in_array($key, $wageItemIds)? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>        
                        </div>
                    </div>  
                    <div class='form-group'>
                        {{ Form::label( 'email', 'Personal Email',['class' => 'col-lg-6 control-label']) }}
                        <div class='col-lg-12'>
                            {{ Form::text('email', null, ['class' => 'form-control round', 'placeholder' => trans('hrms.email')]) }}
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    }
    
    const Form = {
        casual: @json(@$casual),
        docRow: $('#docTbl tbody tr').clone().html(),
        docRowIndx: 0,

        init() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#job_category, #wageItem').select2({allowClear: true});

            $('#job_category').change(Form.categoryChange);
            $('#hasNextKin').change(Form.hasNextKinChange);
            $('#addDoc').click(Form.addDocRow);
            $('#docTbl').on('click', '.remove_doc', Form.delDocRow);

            const data = Form.casual;
            if (data && data.id) {
                if (data.kin_name) {
                    $('#hasNextKin').prop('checked', true).change();
                    $('#kin_relationship').val(data.kin_relationship);
                    $('#kin_name').val(data.kin_name || '');
                    $('#kin_contact').val(data.kin_contact || '');
                    $('#home_address').val(data.home_address || ''); 
                }
            } 
        },

        addDocRow() {
            Form.docRowIndx++;
            let html = Form.docRow.replace(/-0/g, '-' + Form.docRowIndx);
            $('#docTbl tbody').append('<tr>' + html + '</tr>');
        },

        delDocRow() {
            $(this).parents('tr').remove();
            Form.docRowIndx--;
        },

        categoryChange(){
            let rate = $(this).find('option:selected').attr('data-rate');
            $('#rate').val(accounting.unformat(rate));
        },

        hasNextKinChange() {
            const ctn = $('.next-kin-ctn');
            ctn.find('input, select').each(function() {
                $(this).val('').change();
            });
            if ($(this).prop('checked')) {
                ctn.removeClass('d-none');
            } else {
                ctn.addClass('d-none');
            }
        },
    }

    $(Form.init);
</script>
@endsection