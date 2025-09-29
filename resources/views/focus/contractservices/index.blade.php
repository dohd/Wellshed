@extends('core.layouts.app')

@section('title', 'PM Report Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">PM Report Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.contractservices.partials.contractservices-header-buttons')
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
                            <div class="row">
                                <div class="col-3">
                                    <label for="customer">Customer</label>
                                    <select name="customer_id" class="form-control" id="customer" data-placeholder="Choose Customer">
                                        @foreach ($customers as $row)
                                            <option value="{{ $row->id }}">
                                                {{ $row->company }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-5">
                                    <label for="contract">Contract</label>
                                    <select name="contract_id" class="form-control" id="contract">
                                        <option value="">-- select contract --</option>
                                    </select>
                                </div>

                                <div class="col-2">
                                    <label for="schedule">Schedule</label>
                                    <select name="schedule_id" class="form-control" id="schedule">
                                        <option value="">-- select schedule --</option>
                                    </select>
                                </div>

                                <div class="col-2">
                                    <label for="branch">Branch</label>
                                    <select name="branch_id" class="form-control" id="branch">
                                        <option value="">-- select branch --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="serviceTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer - Branch</th>
                                        <th>Contract - Schedule</th>
                                        <th>Bill Amount</th>
                                        <th>Unit Count</th>
                                        <th>Jobcard No</th>
                                        <th>Date</th>
                                        <th>Action</th>
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
{{ Html::script('focus/js/select2.min.js') }}
<script>
    config = {
        ajax: {
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        },
        select: {allowClear: true},
    };
    

    const Index = {
        contracts: @json($contracts),
        schedules: @json($schedules),
        branches: @json($branches),

        init() {
            $.ajaxSetup(config.ajax);
            $('#customer').select2(config.select).val('').change();

            this.drawDataTable();
            $('#customer').change(this.customerChange);
            $('#contract').change(this.contractChange);
            $('#schedule').change(this.scheduleChange);
            $('#branch').change(this.branchChange);
        },

        customerChange() {
            const customer_id = $(this).val();

            $('#contract option:not(:first)').remove();
            contracts = Index.contracts.filter(v => v.customer_id == customer_id);
            contracts.forEach(v => $('#contract').append(`<option value="${v.id}">${v.title}</option>`));

            $('#branch option:not(:first)').remove();
            branches = Index.branches.filter(v => v.customer_id == customer_id);
            branches.forEach(v => $('#branch').append(`<option value="${v.id}">${v.name}</option>`));
            
            $('#serviceTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        contractChange() {
            const contract_id = $(this).val();

            $('#schedule option:not(:first)').remove();
            schedules = Index.schedules.filter(v => v.contract_id == contract_id);
            schedules.forEach(v => $('#schedule').append(`<option value="${v.id}">${v.title}</option>`));

            $('#serviceTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        scheduleChange() {
            $('#serviceTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        branchChange() {
            $('#serviceTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        drawDataTable() {
            $('#serviceTbl').dataTable({
                stateSave: true,
                serverside: true,
                processing: true,
                responsive: true,
                language: {
                    @lang("datatable.strings")
                },
                ajax: {
                    url: '{{ route("biller.contractservices.get") }}',
                    type: 'POST',
                    data: {
                        customer_id: $('#customer').val(),
                        contract_id: $('#contract').val(),
                        schedule_id: $('#schedule').val(),
                        branch_id: $('#branch').val(),
                    }
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'client',
                        name: 'client'
                    },
                    {
                        data: 'contract',
                        name: 'contract'
                    },
                    {
                        data: 'bill',
                        name: 'bill'
                    },
                    {
                        data: 'unit',
                        name: 'unit'
                    },
                    {
                        data: 'jobcard_no',
                        name: 'jobcard_no'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [3] },
                    { type: "custom-date-sort", targets: [6] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        },
    };

    $(() => Index.init());
</script>
@endsection