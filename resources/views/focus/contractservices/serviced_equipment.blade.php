@extends('core.layouts.app')

@section('title', 'PM Report Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">PM Report Serviced Equipments</h4>
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
                            <div class="row form-group">
                                <div class="col-4">
                                    <select name="customer_id" class="form-control" id="customer" data-placeholder="Choose Customer">
                                        @foreach ($customers as $row)
                                            <option value="{{ $row->id }}">
                                                {{ $row->company }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-8">
                                    <select name="contract_id" class="form-control" id="contract" data-placeholder="Choose Contract">
                                    </select>
                                </div>                                
                            </div>
                            <div class="row form-group">
                                <div class="col-4">
                                    <select name="branch_id" class="form-control" id="branch" data-placeholder="Choose Branch">
                                    </select>
                                </div>
                                <div class="col-3">
                                    <select name="schedule_id" class="form-control custom-select" id="schedule">
                                        <option value="">-- Select schedule --</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <select name="status" class="form-control custom-select" id="status">
                                        <option value="">-- Select Equipment status --</option>
                                        @foreach (['working', 'faulty', 'cannibalised', 'decommissioned'] as $val)
                                            <option value="{{ $val }}" {{ $val == $row->status? 'selected' : ''  }}>
                                                {{ ucfirst($val) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-2">
                                    <button class='btn btn-primary' id="loadEquip">
                                        <i class="fa fa-refresh" aria-hidden="true"></i> Load Equipments
                                    </button>
                                </div>
                                <div class="col-2 ml-auto">
                                    <label for="amount">Total Service Amount</label>
                                    {{ Form::text('amount_total', null, ['class' => 'form-control', 'id' =>'amount_total',  'readonly']) }}
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
                                    @php
                                        $col_labels = [
                                            'Branch', 'Building', 'Floor', 'Location', 'Equipment Category', 'Make / Unit Type',
                                            'Model & Model No', 'Size / Capacity', 'Serial No', 'Tag No', 'Gas', 'Rate (VAT Exc)',
                                            'Status', 'Comment', 'Jobcard No', 'Jobcard Date'
                                        ];
                                    @endphp
                                    <th>#</th>
                                    @foreach ($col_labels as $val)
                                        <th>{{ $val }}</th>
                                    @endforeach
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
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        select: {allowClear: true},
    };

    const Index = {
        customers: @json($customers),
        contracts: @json($contracts),
        branches: @json($branches),
        schedules: @json($schedules),

        init() {
            $.ajaxSetup(config.ajax);
            $('#customer').select2(config.select).val('').change();
            $('#branch').select2(config.select);
            $('#contract').select2(config.select);

            this.drawDataTable();
            $('#customer').change(this.customerChange);
            // $('#branch').change(this.branchChange);
            $('#contract').change(this.contractChange);
            // $('#schedule').change(this.scheduleChange);
            // $('#status').change(this.statusChange);
            $('#loadEquip').click(this.LoadEquipClick);
        },

        customerChange() {
            const customer_id = $(this).val();

            $('#branch').html('');
            const branches = Index.branches.filter(v => v.customer_id == customer_id);
            branches.forEach(v => $('#branch').append(`<option value="${v.id}">${v.name}</option>`));
            $('#branch').val('').change();

            $('#contract').html('');
            const contracts = Index.contracts.filter(v => v.customer_id == customer_id);
            contracts.forEach(v => $('#contract').append(`<option value="${v.id}">${v.title}</option>`));
            $('#contract').val('').change();

            if (!customer_id) 
                return Index.LoadEquipClick();
        },

        contractChange() {
            $('#schedule option:not(:first)').remove();
            const schedules = Index.schedules.filter(v => v.contract_id == $(this).val());
            schedules.forEach(v => $('#schedule').append(`<option value="${v.id}">${v.title}</option>`));
        },

        LoadEquipClick() {
            setTimeout(() => {
                $('#serviceTbl').DataTable().destroy();
                Index.drawDataTable();
            }, 1000);
        },

        drawDataTable() {
            $('#serviceTbl').dataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                language: { @lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.contractservices.get_equipments") }}',
                    type: 'POST',
                    data: {
                        customer_id: $('#customer').val(),
                        contract_id: $('#contract').val(),
                        branch_id: $('#branch').val(),
                        schedule_id: $('#schedule').val(),
                        status: $('#status').val(),
                    },
                    dataSrc: ({data}) => {
                        $('#amount_total').val('');
                        if (data.length) $('#amount_total').val(data[0].sum_total);                            
                        return data;
                    },
                },
                columns: [
                    {data: 'DT_Row_Index',name: 'id'},
                    ...[
                        'branch', 'building', 'floor', 'location', 'category', 'make_type',
                        'model', 'capacity', 'equip_serial', 'unique_id', 'machine_gas', 'service_rate',
                        'status', 'note', 'jobcard', 'jobcard_date'
                    ].map(v => ({data: v, name: v})),                    
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: [3] },
                    { type: "custom-date-sort", targets: [6] }
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
            });
        },
    };

    $(() => Index.init());
</script>
@endsection