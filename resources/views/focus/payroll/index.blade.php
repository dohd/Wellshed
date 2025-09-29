@extends ('core.layouts.app')

@section ('title', 'Payroll Management')

@section('page-header')
    <h1>Payroll Management</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">Payroll Management</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.payroll.partials.payroll-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="form-group row px-5 mt-2">
                                <div class="col-3">
                                    <label for="load_payroll">Payroll Month</label>
                                    {{ Form::month('month', null, ['class' => 'form-control month']) }}
                                </div>
                            </div>
                            <div class="card-content mt-3">

                                <div class="card-body">
                                    <table id="payroll-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Payroll No</th>
                                            <th>Processing Date</th>
                                            <th>Basic Salary</th>
                                            <th>Tx Allowance</th>
                                            <th>Tx Deductions</th>
                                            <th>Total NSSF</th>
                                            <th>Total PAYE</th>
                                            <th>Total SHIF</th>
                                            <th>Status</th>
                                            <th>Total NETPAY</th>
                                            <th>{{ trans('labels.general.actions') }}</th>
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i
                                                        class="fa fa-spinner spinner"></i></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}

    <script>

        const config = {
            ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}" }}
        };

        $(document).ready(function() {
            // Use event delegation to handle the click event for dynamically added elements
            $(document).on('click', '.payroll-delete', function(e) {

                e.preventDefault();
                let buttonId = $(this).attr('id');
                let confirmAction = confirm("Are you sure you want to delete this payroll? This action cannot be reversed past this point.");
                if (confirmAction) {
                    // Use the button ID in the URL
                    window.location.href = '/payroll-delete/' + buttonId;
                }
            });
        });

        const Index = {
            month: @json(request('month')),

            init() {
                this.draw_data();
                $('.month').val(this.month).change(this.monthChange);
            },
            monthChange() {
                Index.month = $(this).val();
                $('#payroll-table').DataTable().destroy();
                return Index.draw_data();
            },
            draw_data(){
                $('#payroll-table').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route("biller.payroll.get") }}',
                        type: 'post',
                        data: {
                            month: this.month,
                        },
                    },
                    columns: [
                        {data: 'DT_Row_Index', name: 'id'},
                        {data: 'tid', name: 'tid'},
                        {data: 'processing_date', name: 'processing_date'},
                        {data: 'salary_total', name: 'salary_total'},
                        {data: 'allowance_total', name: 'allowance_total'},
                        {data: 'deduction_total', name: 'deduction_total'},
                        {data: 'total_nssf', name: 'total_nssf'},
                        {data: 'paye_total', name: 'paye_total'},
                        {data: 'total_nhif', name: 'total_nhif'},
                        {data: 'status', name: 'status'},
                        {data: 'total_salary_after_bnd', name: 'total_salary_after_bnd'},
                        {data: 'actions', name: 'actions', searchable: false, sortable: false}
                    ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: {
                        buttons: [

                            {extend: 'csv', footer: true, exportOptions: {columns: [0, 1]}},
                            {extend: 'excel', footer: true, exportOptions: {columns: [0, 1]}},
                            {extend: 'print', footer: true, exportOptions: {columns: [0, 1]}}
                        ]
                    }
                });
            },
        };

        $(() => Index.init());
    </script>
@endsection


<style>
    /* Styling for the modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.75);
    }

    .radius-8-right {
        border-radius: 0 8px 8px 0;
    }
    .radius-8-left {
        border-radius: 8px 0 0 8px;
    }
    .radius-8 {
        border-radius: 8px;
    }


</style>

