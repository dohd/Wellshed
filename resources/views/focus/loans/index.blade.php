@extends ('core.layouts.app')

@section('title', 'Loans Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Loans Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.loans.partials.loans-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="loansTbl" class="table table-striped table-bordered zero-configuration"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Lender</th>
                                        <th>Borrower</th>
                                        <th>Application Date</th>
                                        <th>Loan Amount</th>
                                        <th>Approval Status</th>
                                        <th>Period (Months)</th>
                                        <th>Monthly Installment</th>
                                        <th>Interest</th>
                                        <th>Amount Paid</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1">
                                            <i class="fa fa-spinner spinner"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <form id="approveLoan"></form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    const dataTable = $('#loansTbl').dataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: '{{ route('biller.loans.get') }}',
            type: 'post'
        },
        columns: [
            {
                data: 'DT_Row_Index',
                name: 'id'
            },
            {
                data: 'lender',
                name: 'lender'
            },
            {
                data: 'borrower',
                name: 'borrower'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'amount',
                name: 'amount'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'month_period',
                name: 'month_period'
            },
            {
                data: 'installment',
                name: 'installment'
            },
            {
                data: 'interest',
                name: 'interest'
            },
            {
                data: 'amountpaid',
                name: 'amountpaid'
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            },
        ],
        order: [
            [0, "desc"]
        ],
        searchDelay: 500,
        dom: 'Blfrtip',
        buttons: ['csv', 'excel', 'print'],
        // columnDefs: [{
        //         // For Responsive
        //         className: 'control',
        //         orderable: false,
        //         responsivePriority: 2,
        //         targets: 0,
        //         render: function(data, type, full, meta) {
        //             return '';
        //         }
        //     },


        // ],
        // // responsive popup
        // responsive: {
        //     details: {
        //         display: $.fn.dataTable.Responsive.display.modal({
        //             header: (row) => {
        //                 return `Loan Details For ${row.data()['lender']}`;
        //             }
        //         }),
        //         type: 'column',
        //         renderer: (api, rowIdx, columns) => {
        //             var data = $.map(columns, function(col, i) {
        //                 return col.columnIndex !==
        //                     6 // ? Do not show row in modal popup if title is blank (for check box)
        //                     ?
        //                     '<tr data-dt-row="' +
        //                     col.rowIdx +
        //                     '" data-dt-column="' +
        //                     col.columnIndex +
        //                     '">' +
        //                     '<td>' +
        //                     col.title +
        //                     ':' +
        //                     '</td> ' +
        //                     '<td>' +
        //                     col.data +
        //                     '</td>' +
        //                     '</tr>' :
        //                     '';
        //             }).join('');
        //             return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
        //         }
        //     }
        // },
    });
</script>
@endsection
