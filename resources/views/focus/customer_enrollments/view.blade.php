@extends ('core.layouts.app')

@section('title', 'View Customer Enrollment')

@section('page-header')
    <h1>
        <small>View Customer Enrollment</small>
    </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h3 class="content-header-title mb-0">View Customer Enrollment</h3>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customer_enrollments.partials.customer_enrollments-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                @if ($customer_enrollment['status'] !== 'approved')
                                    
                                <a href="#" class="btn btn-warning btn-sm mr-1" data-toggle="modal"
                                    data-target="#statusModal">
                                    <i class="fa fa-pencil" aria-hidden="true"></i> Service / Product Status
                                </a>
                                @endif
                                @if ($customer_enrollment['payment_status'] !== 'paid' && $customer_enrollment['status'] === 'approved')
                                    
                                <a href="#" class="btn btn-primary btn-sm mr-1" data-toggle="modal"
                                    data-target="#notifyModal">
                                    <i class="fa fa-money" aria-hidden="true"></i> Payment Status
                                </a>
                                @endif
                            </div>
                            <div class="card-content">

                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Client Name</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment->customer ? $customer_enrollment->customer['company'] : $customer_enrollment['name'] }}</p>
                                        </div>
                                        <input type="hidden" value="{{ $customer_enrollment['id'] }}" id="customer_enrollment_id">
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Client Phone</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment->customer ? $customer_enrollment->customer['phone'] : $customer_enrollment['phone'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Client Email</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment->customer ? $customer_enrollment->customer['email'] : $customer_enrollment['email'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Redeemable Code</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment['redeemable_code'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Note</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment['note'] }}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <h2>Service / Product Purchase Status</h2>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ ucfirst($customer_enrollment['status']) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment['date'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Quoted Amount</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment['quote_amount'] }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Remarks</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ $customer_enrollment['status_note'] }}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <h2>Customer Payment Status</h2>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Payment Status</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ ucfirst($customer_enrollment['payment_status']) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Payment Date</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ ucfirst($customer_enrollment['payment_date']) }}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 border-blue-grey border-lighten-5  p-1">
                                            <p>Payment Note</p>
                                        </div>
                                        <div class="col border-blue-grey border-lighten-5  p-1 font-weight-bold">
                                            <p>{{ ucfirst($customer_enrollment['payment_note']) }}</p>
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <table id="commissionsTbl" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            {{-- <th><input type="checkbox" id="selectAll"></th> --}}
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Commission</th>
                                            <th>Actual Commission</th>
                                            <th>Payment Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customer_enrollment->items as $i => $item)
                                            <tr>
                                                {{-- <td>
                                                    @if ($item->payment_status != 'paid')
                                                        <input type="checkbox" class="row-check" data-id="{{ $item->id }}">
                                                    @endif
                                                </td> --}}
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->phone }}</td>
                                                <td>{{ $item->raw_commission }}</td>
                                                <td>{{ $item->actual_commission }}</td>
                                                <td>
                                                    {{ str_replace('_',' ', ucfirst($item->payment_status)) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                {{-- <button id="submitSelected" class="btn btn-primary mt-2">Submit Selected</button> --}}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('focus.customer_enrollments.partials.service_status')
        @include('focus.customer_enrollments.partials.notify_agents')
    </div>
@endsection
@section('extra-scripts')
    <script>
        const config = {
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
        };
        const Index = {
            init() {
                $.ajaxSetup(config.ajax);
                $(document).ready(function() {
                    // Toggle all checkboxes
                    $('#selectAll').on('change', function() {
                        $('.row-check').prop('checked', this.checked);
                    });

                    // If any checkbox is unchecked, uncheck "selectAll"
                    $(document).on('change', '.row-check', function() {
                        if (!this.checked) {
                            $('#selectAll').prop('checked', false);
                        }
                    });

                    // Submit selected IDs
                    $('#submitSelected').on('click', function() {
                        let selectedIds = [];
                        let customer_enrollment_id = $('#customer_enrollment_id').val();
                        $('.row-check:checked').each(function() {
                            selectedIds.push($(this).data('id'));
                        });

                        if (selectedIds.length === 0) {
                            alert("Please select at least one row.");
                            return;
                        }

                        // 🔥 AJAX to backend
                        $.ajax({
                            url: "{{ route('biller.customer_enrollments.change_payment_status') }}", // <-- adjust route
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                customer_enrollment_id: customer_enrollment_id,
                                ids: selectedIds
                            },
                            success: function(response) {
                                alert("Submitted successfully!");
                                console.log(response);
                            },
                            error: function(xhr) {
                                alert("Something went wrong!");
                                console.log(xhr.responseText);
                            }
                        });
                    });
                });
                $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            },
        };
        $(() => Index.init());
    </script>
@endsection
