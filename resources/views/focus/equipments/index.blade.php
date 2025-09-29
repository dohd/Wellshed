@extends ('core.layouts.app')

@section ('title', 'Equipment Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Equipment Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.equipments.partials.equipments-header-buttons')
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
                                <div class="col-4">
                                    <label for="customer">Customer</label>
                                    <select name="customer" class="form-control" id="customer" data-placeholder="Choose Customer">
                                        @foreach ($customers as $row)
                                            <option value=""></option>
                                            <option value="{{ $row->id }}" {{ $row->id == request('customer_id')? 'selected' : '' }}>
                                                {{ $row->company }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label for="branch">Branch</label>
                                    <select name="branch" class="form-control" id="branch" data-placeholder="Choose Branch">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="equipTbl" class="table table-striped table-bordered zero-configuration" width="100%" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Equipment No</th>
                                        <th>Customer - Branch</th>
                                        <th>Capacity</th>
                                        <th>Make - Type</th>
                                        <th>Location</th>   
                                        <th>Gas</th>  
                                        <th>Serial</th>                               
                                        <th>Model</th> 
                                        <th>PM Duration</th>
                                        <th>Status</th>   
                                        <th>{{ trans('labels.general.actions') }}</th>
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
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
        select: {allowClear: true},

    };

    const Index = {
        queryString: @json(request()->getQueryString()),
        branches: @json($branches),
        customerId: @json(request('customer_id')) || '',

        init() {
            $.ajaxSetup(config.ajax);
            $('#customer').select2(config.select);
            $('#branch').select2(config.select);

            this.drawDataTable();
            $('#customer').change(this.customerChange);
            $('#branch').change(this.branchChange);
        },

        customerChange() {
            const customer_id = $(this).val();

            $('#branch').html('');
            const branches = Index.branches.filter(v => v.customer_id == customer_id);
            branches.forEach(v => $('#branch').append(`<option value="${v.id}">${v.name}</option>`));
            $('#branch').val('').change();

            $('#equipTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        branchChange() {
            $('#equipTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        drawDataTable() {
            $('#equipTbl').dataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.equipments.get') }}?" + this.queryString,
                    type: 'POST',
                    data: {
                        customer_id: $('#customer').val() || this.customerId, 
                        branch_id: $('#branch').val(),
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'tid', name: 'tid'},
                    {data: 'customer', name: 'customer'},
                    {data: 'capacity', name: 'capacity'},
                    {data: 'make_type', name: 'make_type'},
                    {data: 'location', name: 'location'},
                    {data: 'machine_gas', name: 'machine_gas'},
                    {data: 'equip_serial', name: 'equip_serial'},
                    {data: 'model', name: 'model'},
                    {data: 'pm_duration', name: 'pm_duration'},
                    {data: 'status', name: 'status'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
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
        }
    };

    $(() => Index.init());
</script>
@endsection
