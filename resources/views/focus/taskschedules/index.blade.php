@extends('core.layouts.app')

@section('title', 'Schedule Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Schedule Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                @include('focus.taskschedules.partials.taskschedule-header-buttons')
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
                                <div class="col-md-6">
                                    <label for="customer">Customer</label>
                                    <select name="customer_id" class="form-control" id="customer" data-placeholder="Choose Customer">
                                        @foreach ($customers as $row)
                                            <option value="{{ $row->id }}">
                                                {{ $row->company }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="contract">Contract</label>
                                    <select name="contract_id" class="form-control" id="contract" data-placeholder="Choose Contract">
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="status">Service Status</label>
                                    <select name="service_status" id="service_status" class="custom-select">
                                        <option value="">-- select status --</option>
                                        @foreach (['unserviced', 'partially_serviced', 'serviced'] as $status)
                                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>  
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="scheduleTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Schedule</th>
                                        <th>Customer</th>
                                        <th>Service Status</th>
                                        <th>Service Amount</th>
                                        <th>Invoice Amount</th>
                                        <th>Start Date</th>
                                        <th>Actual Start Date</th>
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
        ajax: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        select: {allowClear: true},
    };

    const Index = {
        contracts: @json($contracts),

        init() {
            $.ajaxSetup(config.ajax);
            $('#customer').select2(config.select).val('').change();
            $('#contract').select2(config.select).val('').change();

            this.drawDataTable();
            $('#customer').change(this.customerChange);
            $('#contract').change(this.contractChange);
            $('#service_status').change(this.serviceStatusChange);
        },

        customerChange() {
            const customer_id = $(this).val();

            $('#contract option').remove();
            contracts = Index.contracts.filter(v => v.customer_id == customer_id);
            contracts.forEach(v => $('#contract').append(`<option value="${v.id}">${v.title}</option>`));
            $('#contract').val('');
            
            $('#scheduleTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        contractChange() {
            $('#scheduleTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        serviceStatusChange() {
            $('#scheduleTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        drawDataTable() {
            $('#scheduleTbl').dataTable({
                stateSave: true,
                serverside: true,
                processing: true,
                responsive: true,
                language: {@lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.taskschedules.get") }}',
                    type: 'POST',
                    data: {
                        customer_id: $('#customer').val(),
                        contract_id: $('#contract').val(),
                        equip_status: $('#service_status').val(),
                    },
                    dataSrc: ({data}) => {
                        const status = $('#service_status').val();
                        if (status == 'partially_serviced') {
                            data = data.filter(v => v.service_status == 'partial');
                        } else if (status == 'serviced') {
                            data = data.filter(v => v.service_status == 'complete');
                        }
                        return data;
                    },
                },
                columns: [
                    {
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'contract',
                        name: 'contract'
                    },
                    {
                        data: 'loaded',
                        name: 'loaded'
                    },
                    {
                        data: 'total_rate',
                        name: 'total_rate'
                    },
                    {
                        data: 'total_charged',
                        name: 'total_charged'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'actual_startdate',
                        name: 'actual_startdate'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        searchable: false,
                        sortable: false
                    }
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [4, 5] },
                    { type: "custom-date-sort", targets: [6, 7] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons:  [ 'csv', 'excel', 'print'],
            });
        },
    };

    $(() => Index.init());
</script>
@endsection
