<div class="row">
    <div class="col-md-2">
        <div class='form-group'>
            <label for="earningType">Earning Type</label>
            <select name="earning_type" id="earningType" class="custom-select">
                <option value="regular_pay">Regular Pay</option>
                <option value="overtime">Overtime</option>
                <option value="bonus">Bonus</option>
                <option value="allowance">Allowance</option>
                <option value="misc">Miscellaneous</option>
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class='form-group'>
            <label for="name">Column Label</label>
            {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => 'required', 'placeholder' => 'Regular Pay']) }}
        </div>
    </div>
    <div class="col-md-2">
        <div class='form-group'>
            <label for="name">Standard Rate</label>
            {{ Form::text('std_rate', '', ['class' => 'form-control', 'id' => 'stdRate', 'placeholder' => '0.00']) }}
        </div>
    </div>
</div>

<!-- Overtime Rates -->
<div class="row d-none">
    <div class="col-md-4">
        <table id="overtimeTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Weekday OT</td>
                    <td>{{ Form::text('weekday_ot', null, ['class' => 'form-control']) }}</td>
                </tr>
                <tr>
                    <td>Weekend Sat OT<br>Weekend Sun OT</td>
                    <td>
                        {{ Form::text('weekend_sat_ot', null, ['class' => 'form-control']) }}
                        {{ Form::text('weekend_sun_ot', null, ['class' => 'form-control']) }}
                    </td>
                </tr>
                <tr>
                    <td>Holiday OT</td>
                    <td>{{ Form::text('holiday_ot', null, ['class' => 'form-control']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@section('after-scripts')
<script type="text/javascript">
    $('table tbody td').css({padding: '5px 5px 5px 5px'});
    $('table tbody input.form-control').css({maxWidth: '100px', 'height': '25px'});

    const Form = {
        wageItem: @json(@$wageItem),

        init() {
            $('#earningType').change(Form.changeEarningType);

            const data = Form.wageItem;
            if (data && data.id) {
                $('#earningType').val(data.earning_type).change();
                if (data.weekend_ot_sun) $('#checkSun').prop('checked', true);
                if (data.weekend_ot_sat) $('#checkSat').prop('checked', true);
                $('#stdRate').val(accounting.formatNumber(+data.std_rate));
            }
        },

        changeEarningType() {
            $('#name').attr('placeholder', $(this).find(':selected').html());
            const row = $('#overtimeTbl').parents('.row:first');
            if ($(this).val() == 'overtime') {
                row.removeClass('d-none');
                $('#stdRate').val('');
                $('#stdRate').parents('.form-group:first').addClass('d-none');
            }  else {
                row.addClass('d-none');
                $('#stdRate').val('');
                $('#stdRate').parents('.form-group:first').removeClass('d-none');
            }
        },
    };

    $(Form.init);
</script>
@endsection