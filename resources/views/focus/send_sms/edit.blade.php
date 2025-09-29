@extends ('core.layouts.app')

@section ('title', 'Edit SMS')

@section('page-header')
    <h1>
        <small>Edit SMS</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Edit SMS</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.send_sms.partials.send_sms-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    {{ Form::model($send_sms, ['route' => ['biller.send_sms.update', $send_sms], 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'PATCH', 'id' => 'edit-department']) }}

                                    <div class="form-group">
                                        {{-- Including Form blade file --}}
                                        @include("focus.send_sms.form")
                                        <div class="edit-form-btn">
                                            {{ link_to_route('biller.send_sms.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-danger btn-md']) }}
                                            {{ Form::submit(trans('buttons.general.crud.update'), ['class' => 'btn btn-primary btn-md']) }}
                                            <div class="clearfix"></div>
                                        </div><!--edit-form-btn-->
                                    </div><!--form-group-->

                                    {{ Form::close() }}
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}

<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };
    const Form = {
        send_sms : @json($send_sms),
        init(){
            $.ajaxSetup(config.ajax);
            // $('.datepicker').datepicker(config.date);
            $('#employee').select2();
            $('#customer').select2();
            $('#supplier').select2();
            $('#labourer').select2();
            $('#prospect').select2();
            $('#prospect_industry').select2();
            $('#project').select2();
            $('#reset-employee').click(Form.resetEmployee);
            $('#reset-customer').click(Form.resetCustomer);
            $('#reset-supplier').click(Form.resetSupplier);
            $('#reset-labourer').click(Form.resetLabourer);
            $('#reset-prospect').click(Form.resetProspect);

            $('#user_type').change(Form.userTypeChange);
            $('#delivery_type').change(Form.deliveryTypeChange);
            $('#prospect_industry').change(Form.prospectIndustryChange);
            $('#project').change(Form.projectChange);
            if(Form.send_sms){
                $('#schedule_date').prop('disabled',false);
                if (Form.send_sms.scheduled_date) $('#schedule_date').val(Form.send_sms.scheduled_date);
            }
            var costPer160 = 0.60;

            function calculateCost() {
                var company_name = $('#company').val();
                var count_char = company_name.length;
                var length = $('#subject').val().length;
                
                var total_length = count_char + length;
                var messageParts = Math.ceil(total_length / 160);
                var cost = messageParts * costPer160;
                
                $('#charCount').text(total_length);
                $('#characters').val(total_length);
                $('#cost').text(cost.toFixed(2));
                $('#t_cost').val(cost.toFixed(2));

                var count = parseFloat($('#selected_count').text()); 
                var total_cost = count * cost;
                $('#total_cost').val(total_cost);
                $('#total').text(total_cost);
            }

            // Trigger the calculation on page reload
            calculateCost();

            // Trigger the calculation on input event
            $('#subject').on('input', function() {
                calculateCost();
            });
            
            $('#employee').on('change', function() {
                Form.selectedEmployee();  // Display the count
            });
            $('#customer').on('change', function() {
                Form.selectedCustomer();  // Display the count
            });
            $('#supplier').on('change', function() {
                Form.selectedSupplier();  // Display the count
            });
            $('#labourer').on('change', function() {
                Form.selectedLabourer();  // Display the count
            });
            $('#prospect').on('change', function() {
                Form.selectedProspect();  // Display the count
            });
        },

        projectChange(){
            let project_id = $(this).val();
            $.ajax({
                url: "{{route('biller.send_sms.get_casuals')}}",
                method: "POST",
                data: { project_id: project_id},
                success: function(data) {
                    var select = $('#labourer');
                    select.empty();

                    if (data.length === 0) {
                        select.append($('<option>', {
                            value: null,
                            text: 'No Casuals available'
                        }));
                    } else {
                        $.each(data, function(index, option) {
                            select.append($('<option>', { 
                                value: option.id,
                                text : option.name,
                                
                            }));
                        });
                    }
                }
            });
        },

        selectedEmployee() {
            var selectedItems = $('#employee').val();  // This returns an array of selected values
            var count = selectedItems ? selectedItems.length : 0; // Get the length of the array
            // console.log(count);
            $('#selected_count').text(count);  // Display the count
            $('#user_count').val(count); 
            var cost = $('#t_cost').val(); 
            var total_cost = count * cost;
            $('#total_cost').val(total_cost);
            $('#total').text(total_cost);

        },
        selectedCustomer() {
            var selectedItems = $('#customer').val();  // This returns an array of selected values
            var count = selectedItems ? selectedItems.length : 0; // Get the length of the array
            // console.log(count);
            $('#selected_count').text(count);  // Display the count
            $('#user_count').val(count); 
            var cost = $('#t_cost').val(); 
            var total_cost = count * cost;
            $('#total_cost').val(total_cost);
            $('#total').text(total_cost);

        },
        selectedSupplier() {
            var selectedItems = $('#supplier').val();  // This returns an array of selected values
            var count = selectedItems ? selectedItems.length : 0; // Get the length of the array
            // console.log(count);
            $('#selected_count').text(count);  // Display the count
            $('#user_count').val(count); 
            var cost = $('#t_cost').val(); 
            var total_cost = count * cost;
            $('#total_cost').val(total_cost);
            $('#total').text(total_cost);

        },
        selectedLabourer() {
            var selectedItems = $('#labourer').val();  // This returns an array of selected values
            var count = selectedItems ? selectedItems.length : 0; // Get the length of the array
            // console.log(count);
            $('#selected_count').text(count);  // Display the count
            $('#user_count').val(count); 
            var cost = $('#t_cost').val(); 
            var total_cost = count * cost;
            $('#total_cost').val(total_cost);
            $('#total').text(total_cost);

        },
        selectedProspect() {
            var selectedItems = $('#prospect').val();  // This returns an array of selected values
            var count = selectedItems ? selectedItems.length : 0; // Get the length of the array
            // console.log(count);
            $('#selected_count').text(count);  // Display the count
            $('#user_count').val(count); 
            var cost = $('#t_cost').val(); 
            var total_cost = count * cost;
            $('#total_cost').val(total_cost);
            $('#total').text(total_cost);

        },
        prospectIndustryChange(){
            let industry = $(this).val();
            $.ajax({
                url: "{{route('biller.send_sms.get_prospects')}}",
                method: "POST",
                data: { industry: industry},
                success: function(data) {
                    console.log(data);
                    var select = $('#prospect');
                    select.empty();

                    if (data.length === 0) {
                        select.append($('<option>', {
                            value: null,
                            text: 'No prospects available'
                        }));
                    } else {
                        $.each(data, function(index, option) {
                            select.append($('<option>', { 
                                value: option.id,
                                text : option.company,
                                
                            }));
                        });
                    }
                }
            });
        },

        userTypeChange(){
            let user_type = $(this).val();
            if (user_type == 'employee'){
                Form.selectedEmployee();
                $('.div_customer').addClass('d-none');
                $('.div_supplier').addClass('d-none');
                $('.div_employee').removeClass('d-none');
                $('.div_labourer').addClass('d-none');
                $('.div_prospect').addClass('d-none');
            }else if (user_type == 'customer'){
                Form.selectedCustomer();
                $('.div_employee').addClass('d-none');
                $('.div_supplier').addClass('d-none');
                $('.div_customer').removeClass('d-none');
                $('.div_labourer').addClass('d-none');
                 $('.div_prospect').addClass('d-none');
            }else if (user_type == 'supplier'){
                Form.selectedSupplier();
                $('.div_employee').addClass('d-none');
                $('.div_supplier').removeClass('d-none');
                $('.div_customer').addClass('d-none');
                $('.div_labourer').addClass('d-none');
                 $('.div_prospect').addClass('d-none');
            }else if(user_type == 'labourer'){
                Form.selectedLabourer();
                $('.div_employee').addClass('d-none');
                $('.div_supplier').addClass('d-none');
                $('.div_customer').addClass('d-none');
                $('.div_labourer').removeClass('d-none');
                $('.div_prospect').addClass('d-none');
            }else if(user_type == 'prospect'){
                Form.selectedProspect();
                $('.div_employee').addClass('d-none');
                $('.div_supplier').addClass('d-none');
                $('.div_customer').addClass('d-none');
                $('.div_labourer').addClass('d-none');
                $('.div_prospect').removeClass('d-none');
            }
        },

        deliveryTypeChange(){
            let delivery_type = $(this).val();
            if(delivery_type == 'now'){
                $('#schedule_date').prop('disabled',true);
                $('#schedule_date').val('');
            }else if(delivery_type == 'schedule'){
                $('#schedule_date').prop('disabled',false);
                $('#schedule_date').prop('required',true);
            }
        },

        resetEmployee(){
            $('#employee').val(null).trigger('change');
        },
        resetCustomer(){
            $('#customer').val(null).trigger('change');
        },
        resetSupplier(){
            $('#supplier').val(null).trigger('change');
        },
        resetLabourer(){
            $('#labourer').val(null).trigger('change');
        },
        resetProspect(){
            $('#prospect').val(null).trigger('change');
        },
    };
    $(Form.init);
</script>
    
@endsection