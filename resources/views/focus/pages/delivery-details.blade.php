@extends('core.layouts.apps')
@section('title', 'Delivery Details')

@section('content')
<div class="card">
    <div class="card-body">
        <h5>Enter Delivery Details</h5>

        <div class="mb-3">
            <label for="name">Name</label>
            <input type="text" value="{{ $customer->name ?: $customer->company }}" class="form-control" id="customerName">
            <input type="hidden" value="{{ $customer->id }}" class="form-control" id="customer_id">
        </div>

        <div class='mb-3'>
            {{ Form::label('description', 'Description', ['class' => 'control-label']) }} <span class="text-danger">*</span>
            {{ Form::text('description', null, ['class' => 'form-control', 'id' => 'description', 'placeholder' => 'Give a brief description of the order']) }}
        </div>

        <div class="mb-3">
            <label>Order Type</label>
            <select name="order_type" id="order_type" class="form-control" required>
                <option value="">--select order type--</option>
                <option value="one_time">One Time</option>
                <option value="recurring">Recurring</option>
            </select>
        </div>

        <div class='mb-3'>
            {{ Form::label('frequency_type', 'Frequency', ['class' => 'control-label']) }}
            <select name="frequency" id="frequency_type" class="form-control">
                <option value="">--select frequency type--</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="custom">Custom</option>
            </select>
        </div>

        {{-- ✅ DELIVERY DAYS (Unchanged) --}}
        <div class='mb-3'>
            {{ Form::label('delivery_days[]', 'Delivery Days', ['class' => 'control-label']) }}
            @php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            @endphp
            <select name="delivery_days[]" id="delivery_days" class="form-control" multiple>
                <option value="">Select a day</option>
                @foreach ($days as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                @endforeach
            </select>
        </div>

        {{-- ✅ NEW: Locations Linked to Days --}}
        <div class='mb-3'>
            {{ Form::label('customer_zone_ids', 'Locations (match with Delivery Days)', ['class' => 'control-label']) }}
            <div id="location-day-mapping">
                <p class="text-muted">Select delivery days to assign locations.</p>
            </div>
        </div>

        <div class='mb-3'>
            {{ Form::label('week_numbers[]', 'Week Numbers', ['class' => 'control-label']) }}
            @php
                $weeks = [1, 2, 3, 4];
            @endphp
            <select name="week_numbers[]" id="week_numbers" class="form-control" multiple>
                <option value="">Select a Week</option>
                @foreach ($weeks as $week)
                    <option value="{{ $week }}">Week {{ $week }}</option>
                @endforeach
            </select>
        </div>

        <div class='mb-3'>
            {{ Form::label('delivery_date', 'Expected Delivery Date', ['class' => 'control-label']) }}
            {{ Form::date('delivery_date', null, ['class' => 'form-control', 'id' => 'delivery_date', 'placeholder' => 'Expected Delivery Date']) }}
        </div>

        <div class='mb-3'>
            {{ Form::label('start_month', 'Start Date (Period From)', ['class' => 'control-label']) }}
            {{ Form::date('start_month', null, ['class' => 'form-control', 'id' => 'start_month', 'placeholder' => 'Start (Period From)']) }}
        </div>

        <div class='mb-3'>
            {{ Form::label('end_month', 'End Date (Period To)', ['class' => 'control-label']) }}
            {{ Form::date('end_month', null, ['class' => 'form-control', 'id' => 'end_month', 'placeholder' => 'End (Period To)']) }}
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button class="btn btn-light" id="btnBack">← Back</button>
            <button class="btn btn-primary" id="btnNext">Review Order →</button>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
$(function() {
    // Initialize select2
    $('#delivery_days').select2({ allowClear: true, placeholder: "Select delivery days" });
    $('#week_numbers').select2({ allowClear: true, placeholder: "Select week numbers" });

    const customerZones = @json($customer_zones->map(fn($cz) => [
        'id' => $cz->id,
        'name' => @$cz->location->sub_zone_name
    ]));

    // 🔁 Dynamically generate location selects per day
    function renderLocationMapping() {
        const selectedDays = $('#delivery_days').val() || [];
        const container = $('#location-day-mapping');
        container.empty();

        if (selectedDays.length === 0) {
            container.append('<p class="text-muted">Select delivery days to assign locations.</p>');
            return;
        }

        selectedDays.forEach(day => {
            const dayKey = day.replace(/\s+/g, '_').toLowerCase();
            const selectHtml = `
                <div class="mb-2">
                    <label class="form-label fw-bold">${day}</label>
                    <select name="locations_for_days[${dayKey}][]" class="form-control day-location-select" multiple>
                        ${customerZones.map(z => `<option value="${z.id}">${z.name}</option>`).join('')}
                    </select>
                </div>
            `;
            container.append(selectHtml);
        });

        $('.day-location-select').select2({
            placeholder: "Select locations for this day",
            allowClear: true
        });
    }

    $('#delivery_days').on('change', renderLocationMapping);

    // Load stored data
    let stored = JSON.parse(localStorage.getItem("customerDetails") || "{}");

    $("#customerName").val(stored.name || $("#customerName").val());
    $("#description").val(stored.description || $("#description").val());
    $("#customer_id").val(stored.customer_id || $("#customer_id").val());
    $("#order_type").val(stored.order_type || "");
    $("#frequency_type").val(stored.frequency || "");
    $("#delivery_date").val(stored.delivery_date || "");
    $("#start_month").val(stored.start_month || "");
    $("#end_month").val(stored.end_month || "");
    $('#delivery_days').val(stored.delivery_days || []).trigger('change');
    $('#week_numbers').val(stored.week_numbers || []).trigger('change');

    setTimeout(() => {
        if (stored.locations_for_days) {
            Object.entries(stored.locations_for_days).forEach(([dayKey, locs]) => {
                $(`[name="locations_for_days[${dayKey}][]"]`).val(locs).trigger('change');
            });
        }
    }, 500);

    // Show/hide fields based on order type
    function toggleOrderTypeFields() {
        let type = $("#order_type").val();
        if (type === "one_time") {
            $("#frequency_type, #delivery_days, #week_numbers, #start_month, #end_month").closest('.mb-3').hide();
            $("#delivery_date").closest('.mb-3').show();
        } else if (type === "recurring") {
            $("#frequency_type, #start_month, #end_month").closest('.mb-3').show();
            $("#delivery_date").closest('.mb-3').hide();
        } else {
            $("#frequency_type, #delivery_days, #week_numbers, #start_month, #end_month, #delivery_date").closest('.mb-3').hide();
        }
    }

    // Show/hide frequency options
    function toggleFrequencyFields() {
        let frequency = $("#frequency_type").val();
        if (frequency === "daily") {
            $('#delivery_days').val(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']).trigger('change');
            $("#delivery_days").closest('.mb-3').show();
            $("#week_numbers").closest('.mb-3').hide();
        } else if (frequency === "weekly") {
            $("#delivery_days").closest('.mb-3').show();
            $("#week_numbers").closest('.mb-3').hide();
        } else if (frequency === "custom") {
            $("#delivery_days").closest('.mb-3').show();
            $("#week_numbers").closest('.mb-3').show();
        } else {
            $("#delivery_days, #week_numbers").closest('.mb-3').hide();
        }
    }

    toggleOrderTypeFields();
    toggleFrequencyFields();

    $("#order_type").change(() => {
        toggleOrderTypeFields();
        toggleFrequencyFields();
    });

    $("#frequency_type").change(toggleFrequencyFields);

    // Navigation buttons
    $("#btnBack").click(() => history.back());

    $("#btnNext").click(() => {
        let description = $("#description").val().trim();
        if (description === "") {
            alert("Please enter a description before proceeding.");
            $("#description").focus();
            return;
        }

        // Gather day-location mapping
        let locationsForDays = {};
        $('.day-location-select').each(function() {
            const name = $(this).attr('name');
            const match = name.match(/\[(.*?)\]/);
            if (match) {
                locationsForDays[match[1]] = $(this).val() || [];
            }
        });

        let newDetails = {
            name: $("#customerName").val(),
            description: $("#description").val(),
            customer_id: $("#customer_id").val(),
            order_type: $("#order_type").val(),
            frequency: $("#frequency_type").val(),
            delivery_days: $('#delivery_days').val() || [],
            week_numbers: $('#week_numbers').val() || [],
            delivery_date: $("#delivery_date").val(),
            start_month: $("#start_month").val(),
            end_month: $("#end_month").val(),
            locations_for_days: locationsForDays
        };

        localStorage.setItem("customerDetails", JSON.stringify(newDetails));
        window.location.href = "{{ route('biller.customer_pages.review') }}";
    });
});
</script>
@endsection
