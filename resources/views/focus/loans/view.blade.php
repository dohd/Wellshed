@extends ('core.layouts.app')

@section ('title', 'Loans Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Loans Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.loans.partials.loans-header-buttons')
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <a href="#" class="btn btn-info btn-sm mr-1" data-toggle="modal" data-target="#statusModal">
                <i class="fa fa-pencil" aria-hidden="true"></i> Approve
            </a>
        </div>
        <div class="card-body">
            <table id="loansTbl" class="table table-sm table-bordered zero-configuration" cellspacing="0" width="100%">
                <tbody>
                    @php
                        $loan_details = [
                            'Loan No' => $loan->tid,
                            'Lender' => $loan->lender? $loan->lender->name : 'N/A',
                            'Borrower' => $loan->employee? $loan->employee->full_name : 'N/A',
                            'Application Date' => dateFormat($loan->date),
                            'Approval Status' => $loan->approval_status,
                            'Approval Date' => dateFormat($loan->approval_date),
                            'Approval Note' => $loan->approval_note,
                            'Bank Account' => $loan->bank? $loan->bank->holder : '',
                            'Loan Period (Months)' => $loan->month_period,
                            'Loan Amount' => numberFormat($loan->amount + $loan->fee),
                            'Monthly Installment' => numberFormat($loan->month_installment),
                            'Amount Paid' => numberFormat($loan->amountpaid),
                            'Payment Status' => $loan->paid_status,
                            'Note' => $loan->note,
                        ];
                    @endphp
                    @foreach ($loan_details as $key => $val)
                        <tr>
                            <th width="30%">{{ $key }}</th>
                            <td>{{ $val }}</td>
                        </tr> 
                    @endforeach                                      
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('focus.loans.partials.approval_modal')
@endsection

@section('after-scripts')
<script>
    config = {
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    }

    $('#statusModal').on('shown.bs.modal', function() {
        $('.datepicker').datepicker({
            container: '#statusModal',
            ...config.date
        }).datepicker('setDate', new Date());
    });


</script>
@endsection