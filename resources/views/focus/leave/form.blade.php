<div class="form-group row">
    <div class="col-2">
        <label for="employee">Leave Applicant</label>
        <select name="employee_id" id="user" class="form-control" data-placeholder="Search Employee" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ @$leave && $leave->employee_id == $user->id? 'selected' : '' }}>
                    {{ $user->first_name }} {{ $user->last_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-2">
        <label for="category">Leave Category</label>
        <select name="leave_category_id" id="leave_category" class="custom-select">
            <option value="">-- select leave category --</option>
            @isset($leave)
                <option value="{{ $leave->leave_category_id }}" selected>{{ $leave->leave_category->title }}</option>
            @endisset
        </select>
    </div>
    <div class="col-2">
        <label for="title">Viable Leave Days</label>
        {{ Form::text('viable_qty', null, ['class' => 'form-control', 'id' => 'viable_days', 'readonly']) }}
    </div>
    <div class="col-2">
        <label for="days">Leave Start Date</label>
        {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'id' => 'start_date']) }}
    </div>
    <div class="col-2">
        <label for="qty">Leave Duration (Days)</label>
        {{ Form::number('qty', null, ['class' => 'form-control', 'min' => '1', 'id' => 'qty', 'required']) }}
    </div>    
    <div class="col-2">
        <label for="end_date">Leave End Date</label>
        {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'id' => 'end_date','disabled']) }}
    </div>
    </div>

    <div class="form-group row">
        <div class="col-12">
            <label for="title">Reason for Leave Request</label>
            {{ Form::text('reason', null, ['class' => 'form-control', 'id' => 'title', 'required']) }}
        </div>
    </div>

    <div class="form-group row">
        <div class="col-6">
            <label for="assistant">Duties Delegated To</label>
            <select name="assist_employee_id[]" id="assist_user" class="form-control" data-placeholder="Search Employee" multiple>
                @php
                $assist_employee_id = isset($leave)? explode(',',$leave->assist_employee_id): null;
                @endphp
                @foreach ($users->where('id', '!=', auth()->user()->id) as $user)
                    <option value="{{ $user->id }}" 
                        {{ @$leave && in_array($user->id, (array) $assist_employee_id)? 'selected' : '' }}>
                        {{ $user->first_name }} {{ $user->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6">
            <label for="approvers">Search Approvers</label>
            <select name="approver_ids[]" id="approver_ids" class="form-control" data-placeholder="Search Employee" multiple>
                @php
                $approver_ids = isset($leave)? explode(',',$leave->approver_ids): null;
                @endphp
                @foreach ($users->where('id', '!=', auth()->user()->id) as $user)
                    <option value="{{ $user->id }}" 
                        {{ @$leave && in_array($user->id, (array) $approver_ids)? 'selected' : '' }}>
                        {{ $user->first_name }} {{ $user->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
<div class="form-group row">
    <div class="col-12">
        <label for="handover_note">Handover Note</label>
        <textarea name="handover_note" id="handover_note" class="form-control" rows="4">{{ @$leave->handover_note }}</textarea>
    </div>
</div>
<div>
<div>
    <p style="font-weight: bold; font-size: 1.5em;">IMPORTANT</p>
    <p style="color: red;">1. Employee must submit their leave request at least five days before the start of the requested leave period.</p>
    <p style="color: red;">2. Employees with less than 12 months of service are not eligible for paid leave (Employment Act).</p>
    <p style="color: red;"><b>3. Please Send Handover notes to Delegates.</b></p>
</div>

</div>

<div class="form-group row no-gutters">
    <div class="col-1 ml-auto">
        <a href="{{ route('biller.leave.index') }}" class="btn btn-danger block">Cancel</a>    
    </div>
    <div class="col-1 ml-1">
        {{ Form::submit(@$leave? 'Update' : 'Create', ['class' => 'form-control btn btn-primary']) }}
    </div>
</div>

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        leave: @json(@$leave),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#leave_category').change(this.leaveCategoryChange);
            $('#user').select2({allowClear: true});
            $('#assist_user').select2({allowClear: true});
            $('#approver_ids').select2({allowClear: true});

            if (this.leave) {
                $('#start_date').datepicker('setDate', new Date(this.leave.start_date));
                $('#end_date').datepicker('setDate', new Date(this.leave.end_date));
                $('#leave_category').val(this.leave.leave_category_id);
            } else {
                $('#user').val('').change();
                $('#assist_user').val('').change();
            }
            $('#user').change(this.employeeChange);
            $('#qty').change(this.leaveQtyChange);
        },



        leaveQtyChange() {
            const qty = accounting.unformat($(this).val());
            const viableDays = accounting.unformat($('#viable_days').val());
            if (qty > viableDays) $(this).val(viableDays);

            var startDate = $('#start_date').val();
            var days = parseInt(qty, 10);
            var leave_category = $('#leave_category').val();
            const url = "{{ route('biller.leave.get_end_date') }}";
            $.post(url, {start_date: startDate, days: days, leave_category_id: leave_category}, data => {
               console.log(data);
               $('#end_date').val(data);
            });
            
        },

        employeeChange() {
            $('#viable_days').val('');
            $('#leave_category option:not(:eq(0))').remove();
            if (!$(this).val()) return; 

            const url = "{{ route('biller.leave.leave_categories') }}";
            $.post(url, {employee_id: $(this).val()}, data => {
                data.forEach(v => {
                    const opt = `<option value="${v.id}" category_qty="${v.qty}">${v.title}</option>`;
                    $('#leave_category').append(opt);
                });
            });
        },

        leaveCategoryChange() {
            const days = $(this).find(':selected').attr('category_qty');
            $('#viable_days').val(days);
        },
    };

    $(() => Index.init());
</script>
@endsection