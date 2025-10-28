@extends('core.layouts.apps')
@section('title', 'Delivery Details')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Enter Delivery Details</h5>

            <div class="mb-3">
                <label for="name">Name</label>
                <input type="text" value="{{ $customer->name ?: $customer->company }}" class="form-control"
                    id="customerName">
                <input type="hidden" value="{{ $customer->id }}" class="form-control" id="customer_id">
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
                    <option value="bi_weekly">Bi Weekly</option>
                    <option value="monthly">Monthly</option>
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
    <script>
        $(function() {
            // Load saved data
            let stored = JSON.parse(localStorage.getItem("customerDetails") || "{}");

            $("#customerName").val(stored.name || $("#customerName").val());
            $("#customer_id").val(stored.customer_id || $("#customer_id").val());
            $("#order_type").val(stored.order_type || "");
            $("#frequency_type").val(stored.frequency || "");
            $("#delivery_date").val(stored.delivery_date || "");
            $("#start_month").val(stored.start_month || "");
            $("#end_month").val(stored.end_month || "");

            // ✅ Show/Hide Fields Based on Order Type
            function toggleFields() {
                let type = $("#order_type").val();

                if (type === "one_time") {
                    $("#frequency_type").closest('.mb-3').hide();
                    $("#start_month").closest('.mb-3').hide();
                    $("#end_month").closest('.mb-3').hide();
                    $("#delivery_date").closest('.mb-3').show();
                } else if (type === "recurring") {
                    $("#frequency_type").closest('.mb-3').show();
                    $("#start_month").closest('.mb-3').show();
                    $("#end_month").closest('.mb-3').show();
                    $("#delivery_date").closest('.mb-3').hide();
                } else {
                    // If not selected, hide recurring fields
                    $("#frequency_type").closest('.mb-3').hide();
                    $("#start_month").closest('.mb-3').hide();
                    $("#end_month").closest('.mb-3').hide();
                }
            }

            toggleFields(); // initial check
            $("#order_type").change(toggleFields);

            // ✅ Navigation Buttons
            $("#btnBack").click(() => history.back());

            $("#btnNext").click(() => {
                let newDetails = {
                    name: $("#customerName").val(),
                    customer_id: $("#customer_id").val(),
                    order_type: $("#order_type").val(),
                    frequency: $("#frequency_type").val(),
                    delivery_date: $("#delivery_date").val(),
                    start_month: $("#start_month").val(),
                    end_month: $("#end_month").val()
                };

                localStorage.setItem("customerDetails", JSON.stringify(newDetails));
                window.location.href = "{{ route('biller.customer_pages.review') }}";
            });
        });
    </script>

@endsection
