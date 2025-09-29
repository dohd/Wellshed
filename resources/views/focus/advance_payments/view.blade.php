@extends ('core.layouts.app')

@section('title', 'Edit | Advance Payment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Advance Payment Application</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.advance_payments.partials.advance-payments-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-header">
                    <a href="#" class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                        <i class="fa fa-pencil" aria-hidden="true"></i> Status
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $employee_name = '';
                            $employee = $advance_payment->employee;
                            if ($employee) $employee_name = $employee->first_name . ' ' . $employee->last_name;
                        
                            $details = [
                                'Employee' => $employee_name,
                                'Amount' => numberFormat($advance_payment->amount),
                                'Date' => dateFormat($advance_payment->date),
                                'Approval Status' => $advance_payment->status,
                                'Approval Date' => dateFormat($advance_payment->approve_date),
                                'Approved Amount' => numberFormat($advance_payment->approve_amount),
                                'Approval Note' => $advance_payment->approve_note,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('focus.advance_payments.partials.status-modal')
@endsection

@section('extra-scripts')
<script>
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {
            format: "{{ config('core.user_date_format')}}", 
            autoHide: true,
            container: '#statusModal modal-body'
        },
    };

    const View = {
        payment: @json(@$advance_payment),

        init() {
            $('#statusModal').on('shown.bs.modal', this.showModal);
            $('#approve_amount').change(this.amountChange);
            $('#status').change(this.statusChange);
        },

        showModal() {
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            const payment = View.payment;
            if (payment) {
                $('.datepicker').datepicker('setDate', new Date(payment.approve_date));
                if (payment.status == 'approved') $('#status').attr('disabled', true);
            }
        },

        amountChange() {
            const val = accounting.unformat($(this).val());
            $(this).val(accounting.formatNumber(val));
        },

        statusChange() {
            if ($(this).val() == 'rejected') {
                $('#approve_amount').val('').attr('readonly', true);
            } else {
                $('#approve_amount').attr('readonly', false);
            }
        },
    };

    $(() => View.init());
</script>
@endsection