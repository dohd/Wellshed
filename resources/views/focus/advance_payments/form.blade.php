@php use App\Models\hrm\Hrm; @endphp
<div class="form-group row">
    <div class="col-5">
        <label for="employee">Applicant</label>



            <select name="employee_id" id="user" class="form-control" data-placeholder="Search Employee" required>

                @if (access()->allow('advance-payment-super-applicant'))

                    @foreach ($users as $user)
                        <option
                                value="{{ $user->id }}"
                                data-description="{{ $user->limit }}"
                                {{ @$advance_payment && $advance_payment->employee_id == $user->id? 'selected' : '' }}>
                            {{ $user->first_name }} {{ $user->last_name }}
                        </option>
                    @endforeach


                @else

                    @php
                        $advanceUserId = \Illuminate\Support\Facades\Auth::user()->id;
                        $advanceUser = Hrm::where('id', $advanceUserId)
                            ->with('employees_salary')
                            ->get()
                            ->map(function ($user) {

                                return [
                                    'id' => $user->id,
                                    'first_name' => $user->first_name,
                                    'last_name' => $user->last_name,
                                    'max' =>  floatval(optional($user->employees_salary)->basic_salary * 0.7)
                                ];
                            })
                            ->first();

                    @endphp

                    <option
                            value="{{ $advanceUser->id }}"
                            selected
                            data-description="{{ $advanceUser->max }}"
                    >
                        {{ $advanceUser->first_name }} {{ $advanceUser->last_name }}
                    </option>

                @endif

            </select>

    </div>
    <div class="col-4">
        <label for="amount">Amount (Maximum 70% of Basic Salary)</label>
        {{ Form::number('amount', null, ['step' => 0.01, 'class' => 'form-control', 'id' => 'amount', 'required']) }}
    </div>
    <div class="col-3">
        <label for="date">Date</label>
        {{ Form::text('date', null, ['class' => 'form-control datepicker', @$advance_payment ? 'readonly' : '' ]) }}
    </div>
</div>

<div class="row mb-2">
    <div class="col-8">
        <label for="notes">Notes</label>
        <textarea name="notes" id="description" class="col-8 col-lg-8 tinyinput" cols="30" rows="10" placeholder="Provide any additional details"
                  aria-label="Description">

        </textarea>
    </div>
</div>



<div class="form-group row no-gutters mt-5">
    <div class="col-1">
        <a href="{{ route('biller.advance_payments.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$advance_payment? 'Update' : 'Create', ['class' => 'form-control btn btn-primary']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };


    $(() => {
        tinymce.init({
            selector: '.tinyinput',
            menubar: false,
            plugins: 'anchor autolink charmap codesample image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link table | align lineheight | checklist numlist bullist indent outdent | removeformat',
            height: 200,
            license_key: 'gpl'
        });
    });

    // Function to update the description label
    function updateEmployee() {

        const employee = $('#user').val();
        const amount = $('#amount').val();

        if (employee && amount){

            const selectedOption = $('#user option:selected');
            const max = selectedOption.data('description') || '';

            if (max && amount > parseInt(max)) $('#amount').val(max);

            console.clear();
            console.table(max)
        }
    }

    // Call the function on page load in case an option is pre-selected
    updateEmployee();

    // Update the description whenever the selection changes
    $('#amount').on('input', updateEmployee);

    const Index = {
        payment: @json(@$advance_payment),

        init() {
            $.ajaxSetup(config.ajax);
            $('#user').select2({allowClear: true});
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#amount').change(this.amountChange);

            if (this.payment) {
                $('#amount').val(accounting.formatNumber(this.payment.amount));
                $('.datepicker').datepicker('setDate', new Date(this.payment.date));
            } else {
                $('#user').val('').change();
            }
        },

        amountChange() {
            const val = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(val));
        },
    };

    $(() => Index.init());
</script>
@endsection