@extends ('core.layouts.app')

@section('title', 'View | Tax Return Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Tax Return Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.tax_reports.partials.tax-report-header-buttons')
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
                                'Sale/Purchase Month' => $tax_report->record_month,
                                'Return Month' => $tax_report->return_month,
                                'Report Note' => $tax_report->note,
                                'Sale Taxable Amount' => numberFormat($tax_report->sale_subtotal),
                                'Sale Tax' => numberFormat($tax_report->sale_tax),
                                'Sale Total Amount' => numberFormat($tax_report->sale_total),
                                'Purchase Taxable Amount' => numberFormat($tax_report->purchase_subtotal),
                                'Purchase Tax' => numberFormat($tax_report->purchase_tax),
                                'Purchase Total Amount' => numberFormat($tax_report->purchase_total),
                            ];

                            $filed_report_params = [
                                'tax_report_id' => $tax_report->id,
                                'record_month' => $tax_report->record_month,
                                'return_month' => $tax_report->return_month,
                                'tax_group' => $tax_report->tax_group,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>
                                    @if ($key == 'Sale/Purchase Month')
                                        <span class="mr-1">{{ $val }}</span>
                                        <a class="btn btn-purple btn-sm" href="{{ route('biller.tax_reports.filed_report', $filed_report_params) }}" title="field_returns">
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
