@extends('core.layouts.apps')
@section('title', 'Delivery Details')

@section('content')
<div class="card">
    <div class="card-body">
        <h5>Enter Delivery Details</h5>

        {{-- CUSTOMER --}}
        <div class="mb-3">
            <label for="name">Segment</label>
            <input type="text"
                   value="{{ $customer->segment }}"
                   class="form-control" readonly>
            <input type="text"
                   value="{{ $customer->name ?: $customer->company }}"
                   class="form-control d-none"
                   id="customerName" readonly>
            <input type="hidden"
                   value="{{ $customer->id }}"
                   id="customer_id">
        </div>
        <div class="mb-3">
            <label for="addresses">Address</label>
            @foreach ($customer->customer_zones()->with(['location', 'address'])->get() as $zone)
                <input type="text" 
                    value="{{ @$zone->location->sub_zone_name }}:  ({{ @$zone->address->building_name }} || {{ @$zone->address->floor_no }} || {{ @$zone->address->door_no }})" 
                    class="form-control" 
                    readonly
                >
            @endforeach                   
        </div>

        {{-- DESCRIPTION --}}
        <div class='mb-3'>
            {{ Form::label('description', 'Description', ['class' => 'control-label']) }} <span class="text-danger">*</span>
            {{ Form::text('description', null, ['class' => 'form-control', 'id' => 'description', 'placeholder' => 'Give a brief description']) }}
        </div>

        {{-- Hidden order_type (controlled by recurring flag) --}}
        <div class="mb-3 d-none">
            <select name="order_type" id="order_type" class="form-control">
                <option value="">--select order type--</option>
                <option value="one_time">One Time</option>
                <option value="recurring">Recurring</option>
            </select>
        </div>

        {{-- FREQUENCY --}}
        <div class='mb-3'>
            {{ Form::label('frequency_type', 'Frequency', ['class' => 'control-label']) }}
            <select name="frequency" id="frequency_type" class="form-control">
                <option value="">-- Delivery Frequency Type --</option>
                {{-- <option value="daily">Daily</option> --}}
                <option value="weekly">Weekly</option>
                <option value="custom">Custom</option>
            </select>
        </div>

        {{-- DELIVERY DAYS --}}
        <div class='mb-3'>
            {{ Form::label('delivery_days[]', 'Delivery Days', ['class' => 'control-label']) }}
            @php
                $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
            @endphp
            <select name="delivery_days[]" id="delivery_days" class="form-control" multiple>
                @foreach ($days as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                @endforeach
            </select>
        </div>

        {{-- WEEK NUMBERS --}}
        <div class='mb-3'>
            {{ Form::label('week_numbers[]', 'Week Numbers', ['class' => 'control-label']) }}
            @php
                $weeks = [1,2,3,4];
            @endphp
            <select name="week_numbers[]" id="week_numbers" class="form-control" multiple>
                @foreach ($weeks as $week)
                    <option value="{{ $week }}">Week {{ $week }}</option>
                @endforeach
            </select>
        </div>

        {{-- LOCATIONS + Automatic Qty --}}
        <div class='mb-3 div_locations d-none'>
            {{ Form::label('customer_zone_ids', 'Locations & Qty per Day', ['class' => 'control-label']) }}
            <div id="location-day-mapping">
                <p class="text-muted">Select delivery days to assign locations + qty.</p>
            </div>
        </div>

        {{-- DELIVERY DATE (one_time) --}}
        <div class='mb-3'>
            {{ Form::label('delivery_date', 'Expected Delivery Date', ['class' => 'control-label']) }}
            {{ Form::date('delivery_date', null, ['class' => 'form-control', 'id' => 'delivery_date']) }}
        </div>

        {{-- START DATE --}}
        <div class='mb-3'>
            {{ Form::label('start_month', 'Start Date', ['class' => 'control-label']) }}
            {{ Form::date('start_month', null, ['class' => 'form-control', 'id' => 'start_month']) }}
        </div>

        {{-- END DATE --}}
        <div class='mb-3 d-none'>
            {{ Form::label('end_month', 'End Date', ['class' => 'control-label']) }}
            {{ Form::date('end_month', null, ['class' => 'form-control', 'id' => 'end_month']) }}
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

    let recurring = @json($recurring);
    let totalQty  = @json($qty);
    const customerZones = @json($customer_zones->map(fn($cz)=>[
        'id'=>$cz->id,
        'name'=>@$cz->location->sub_zone_name
    ]));

    $('#delivery_days').select2({ allowClear: true, placeholder: "Select delivery days" });
    $('#week_numbers').select2({ allowClear: true, placeholder: "Select week numbers" });

    /* ✅ FIX recurring behavior */
    function toggleOrderTypeFields() {
        let type = recurring == 1 ? "one_time" : "recurring";
        $("#order_type").val(type);

        if (type === "one_time") {
            $("#frequency_type, #delivery_days, #week_numbers, #start_month, #end_month")
                .closest('.mb-3').hide();
            $("#delivery_date").closest('.mb-3').show();
        } else {
            $("#frequency_type, #start_month, #end_month")
                .closest('.mb-3').show();
            $("#delivery_date").closest('.mb-3').hide();
        }
    }

    function toggleFrequencyFields() {
        let frequency = $("#frequency_type").val();

        if (frequency === "daily") {
            $('#delivery_days')
              .val(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])
              .trigger('change');

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

        autoAllocateQty();
    }


    /* ---------- LOCATION RENDER ---------- */
    function renderLocationMapping() {
        const selectedDays = $('#delivery_days').val() || [];
        const container = $('#location-day-mapping');
        container.empty();

        if (selectedDays.length === 0) {
            container.append(`
                <p class="text-muted">
                    Select delivery days to assign locations + qty.
                </p>
            `);
            return;
        }

        $('.div_locations').removeClass('d-none');
        selectedDays.forEach(day => {
            const dkey = day.replace(/\s+/g, '_').toLowerCase();
            container.append(`
                <div class="mb-2 border p-2 rounded" data-day-block="${dkey}">
                    <label class="fw-bold">${day}</label>

                    <select name="locations_for_days[${dkey}][]"
                        class="form-control day-location-select" multiple>
                        ${customerZones.map(z =>
                            `<option value="${z.id}">${z.name}</option>`
                        ).join('')}
                    </select>
                </div>
            `);
        });

        $('.day-location-select').select2({
            placeholder: "Select locations for this day",
            allowClear: true
        });

        autoAllocateQty();
    }


    /* ---------- ✅ Qty Sum Validation ---------- */
    function validateQtySum() {
        let sum = 0;
        $(".qty-day-input").each(function () {
            let v = Number($(this).val()) || 0;
            sum += v;
        });

        if (sum !== Number(totalQty)) {
            $("#qty-warning").remove();
            $("#location-day-mapping").append(`
                <p id="qty-warning" class="text-danger mt-2">
                    Total allocated qty (${sum}) must equal required qty (${totalQty})
                </p>
            `);
            return false;
        }

        $("#qty-warning").remove();
        return true;
    }

    $(document).on("input", ".qty-day-input", function () {
        validateQtySum();
    });


    /* ---------- AUTO ALLOCATE ---------- */
    function autoAllocateQty() {

        if(recurring != 0) return;  

        let frequency = $("#frequency_type").val();
        let days = $("#delivery_days").val() || [];
        let weeks = $("#week_numbers").val() || [];

        $(".qty-day-input").remove();

        if(!totalQty || totalQty <= 0) return;
        if(!frequency || days.length == 0) return;

        let allocations = {};

        if (frequency === 'daily' || frequency === 'weekly') {
            let dcount = days.length;
            let per = Math.floor(totalQty / dcount);
            let rem = totalQty % dcount;

            days.forEach((day) => {
                let v = per;
                if(rem > 0) { v++; rem--; }
                allocations[day] = v;
            });
        }

        else if (frequency === 'custom') {

            let dcount = days.length;
            let wcount = weeks.length;
            let slots = dcount * wcount;

            if(slots == 0) return;

            if(slots > totalQty) {
                alert("week_numbers × delivery_days must be ≤ qty");
                return;
            }

            let per = Math.floor(totalQty / slots);
            let rem = totalQty % slots;

            weeks.forEach(w => {
                days.forEach(day => {
                    let key = `${day}_week_${w}`;
                    let v = per;
                    if(rem > 0) { v++; rem--; }
                    allocations[key] = v;
                });
            });
        }

        for (let key in allocations) {

            if(!key.includes("_week_")) {
                let dkey = key.replace(/\s+/g,'_').toLowerCase();
                $(`[data-day-block='${dkey}']`).append(`
                    <input type="number"
                           class="form-control qty-day-input mt-1"
                           name="qty_for_day[${dkey}]"
                           value="${allocations[key]}">
                `);

            } else {
                let parts = key.split("_week_");
                let day = parts[0];
                let week = parts[1];
                let dkey = day.replace(/\s+/g,'_').toLowerCase();

                $(`[data-day-block='${dkey}']`).append(`
                    <label class="small mt-1">Week ${week}</label>
                    <input type="number"
                           class="form-control qty-day-input"
                           name="qty_for_custom[${dkey}][${week}]"
                           value="${allocations[key]}">
                `);
            }
        }

        validateQtySum();
    }


    /* ---------- INIT ---------- */
    $("#delivery_days").on("change", renderLocationMapping);
    $("#frequency_type, #week_numbers").on("change", autoAllocateQty);

    toggleOrderTypeFields();
    toggleFrequencyFields();
    renderLocationMapping();

    $("#order_type").change(() => {
        toggleOrderTypeFields();
        toggleFrequencyFields();
    });

    $("#frequency_type").change(toggleFrequencyFields);

    $("#btnBack").click(() => history.back());


    /* ---------- ✅ NEXT BUTTON → SAVE TO LOCALSTORAGE ---------- */
    $("#btnNext").click(() => {

        if ($("#description").val().trim() === "") {
            alert("Description is required");
            return;
        }

        if (!validateQtySum()) {
            alert("Total Qty allocation mismatch. Fix before proceeding.");
            return;
        }

        /* ✅ Collect location mapping */
        let locs = {};
        $('.day-location-select').each(function(){
            const name = $(this).attr('name');
            const match = name.match(/\[(.*?)\]/);
            if (match) {
                locs[match[1]] = $(this).val() || [];
            }
        });

        /* ✅ Collect qty for each day/week */
        let qtyData = {};

        $(".qty-day-input").each(function () {
            let name = $(this).attr("name");
            let value = Number($(this).val()) || 0;
            qtyData[name] = value;
        });


        /* ✅ Build full object */
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
            locations_for_days: locs,

            // ✅ NEW (qty included now)
            qty_per_day: qtyData
        };

        /* ✅ Save in LocalStorage */
        localStorage.setItem("customerDetails", JSON.stringify(newDetails));

        window.location.href = "{{ route('biller.customer_pages.review') }}";
    });

});
</script>

@endsection

