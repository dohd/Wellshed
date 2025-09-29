@extends ('core.layouts.app')

@section ('title',  'CU Invoice Number')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">

        <div class="content-header-left col-6">
            <h3 class="mb-0">Set The Next CU Invoice Number</h3>
        </div>

    </div>
    
    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-radius: 8px;">
                    <div class="card-content">
                        <div class="card-body">
{{--                            {{ Form::open(['route' => 'biller.lead-sources.store', 'method' => 'POST', 'id' => 'create-employee-daily-log']) }}--}}
                            <div class="form-group">




                                <div class="edit-form-btn">

{{--                                    {{ Form::submit(trans('buttons.general.crud.create'), ['class' => 'btn btn-primary btn-md', 'id']) }}--}}




                                    <div class="clearfix"></div>
                                </div>                                    
                            </div>
{{--                            {{ Form::close() }}--}}
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
    // initialize datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format') }}", autoHide: true})
    $('#purchase_date').datepicker('setDate', new Date());
    $('#warranty_expiry_date').datepicker('setDate', new Date());


    $(document).ready(function() {
        var timer;
        $('#cu_no').on('keyup', function() {
            clearTimeout(timer);
            let no = $(this).val();
            let isActive = $('#cuActive').val();

            timer = setTimeout(function() {
                $.ajax({
                    url: "{{ route('biller.check-control-unit-invoice-number') }}",
                    type: 'GET',
                    data: { cuNo: no },
                    success: function(data) {

                        console.table({cuNo: no})
                        console.table(data);

                        if (data.isClear) {
                            // If the response is 'true'
                            $('#cu_no').css('border', '3px solid green');
                            $('#response').css('color', 'green');
                            $('#setNumber').prop('disabled', false);
                            $('#response').text(data.message);
                        }
                        else if (!data.isClear && data.message.length !== 0) {
                            // If the response is 'false'
                            $('#cu_no').css('border', '3px solid red');
                            $('#response').css('color', 'red');
                            $('#setNumber').prop('disabled', true);
                            $('#response').text(data.message);
                        }
                        else if (!data.isClear && data.message.length === 0 && parseInt(no) === 0) {

                            $('#cu_no').css('border', '3px solid red');
                            $('#response').css('color', 'red');
                            $('#setNumber').prop('disabled', true);
                            $('#response').text('Zero is not allowed...');
                        }
                        else if (!data.isClear && data.message.length === 0 && no === '') {

                            $('#setNumber').prop('disabled', false);
                            $('#response').text('');
                            $('#cu_no').css('border', '1px solid gray');
                        }



                    },
                    error: function(error) {
                        // Handle errors here
                        console.log(error);
                    }
                })


            }, 600);
        });


        $('#setNumber').on('click', function() {
            let cuNoValue = $('#cu_no').val(); // assuming '#cu_no' is an input field
            $.ajax({
                url: '{{ route("biller.set-control-unit-invoice-number", "") }}', // <-- named route
                type: 'GET',
                data: {
                    cuNo: cuNoValue,
                    cuActive: $('#cuActive').val()
                },

                success: function(data) {
                    console.log(data);

                    if (data.isSet && data.cu !== false) {

                        console.log('ACTIVATED AND SET');
                        // Handle the success case here
                        $('#result').css('color', 'green');
                    }
                    if (data.isSet && data.cu === 'DEACTIVATED') {
                        // Handle the success case here

                        console.log('DEACTIVATED');

                        $('#result').css('color', 'green');
                        $('#response').text('');
                        $('#cu_no').css('border', '1px solid gray');
                    }
                    else if (!data.isSet && data.cu === false) {
                        console.log('ACTIVATED AND SET');

                        // Handle the failure case here
                        $('#result').css('color', 'red');
                    }

                    $('#result').text(data.message);
                },
                error: function(error) {
                    // Handle errors here
                    console.log(error);
                }
            });
        });    });</script>
@endsection