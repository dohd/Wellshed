@extends ('core.layouts.app')

@section('title', 'Return Acknowledgement Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Return Acknowledgement Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tax_prns.partials.tax-prn-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Return Month' => $tax_prn->return_month,
                                'Return Number' => $tax_prn->return_no,
                                'Return Period' => dateFormat($tax_prn->period_from) . ' || ' . dateFormat($tax_prn->period_to),
                                'Acknowledgement Date' => date('d-M-Y', strtotime($tax_prn->ackn_date)),
                                'Payment Mode' => ucfirst($tax_prn->payment_mode),
                                'Payment Reference' => $tax_prn->payment_ref,
                                'Payment Amount' => numberFormat($tax_prn->amount),
                                'PRN Number' => $tax_prn->prn_no,
                                'PRN Date' => dateFormat($tax_prn->prn_date),
                                'Remark' => $tax_prn->note,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Return Month' && $val)
                                        <span class="mr-1">{{ $val }}</span>
                                        <a class="btn btn-purple btn-sm" href="{{ route('biller.tax_reports.filed_report', ['return_month' => @$tax_prn->return_month ?: '#']) }}" title="Filed Returns">
                                            <i class="fa fa-list"></i> List
                                        </a>  
                                    @else
                                        {{ $val }}
                                    @endif  
                                </td>
                            </tr>                           
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
