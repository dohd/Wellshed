@extends ('core.layouts.app')

@section ('title', 'Business Account Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Business Account Management</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.tenants.partials.tenants-header-buttons')
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
                            <div class="row mb-1">
                                <div class="col-9 col-lg-3">
                                    <label for="statusFilter">Filter by Status</label>
                                    <select class="form-control box-size filter" id="statusFilter"
                                            data-placeholder="Filter by Status">
                                        <option value="">Filter by Status</option>
                                        @foreach (['Active', 'Suspended', 'Onboarding'] as $status)
                                            <option value="{{ $status }}">
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-lg-2">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2"> Clear
                                        Filters
                                    </button>
                                </div>
                            </div>
                                <table id="tenantsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Business Name</th>
                                        <th>Product/Service</th>
                                        <th>Recurrent Cost</th>
                                        <th>Billing Date</th>
                                        <th>Grace Days</th>
                                        <th>Cutoff Date</th>
                                        <th>Balance</th>
                                        <th>Loyalty Points</th>
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
{{-- For DataTables --}}
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script>
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }});


    $('.filter').select2({allowClear: true});

    const statusFilter = $('#statusFilter');
    const clearFilters = $('#clearFilters');

    $('.filter').change(() => {

        $('#tenantsTbl').DataTable().destroy();
        draw_data();
    });

    clearFilters.click(() => {

        statusFilter.val('').trigger('change');

        $('#tenantsTbl').DataTable().destroy();
        draw_data();
    });

    function draw_data() {        
        var dataTable = $('#tenantsTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: { @lang('datatable.strings') },
            ajax: {
                url: '{{ route("biller.tenants.get") }}',
                type: 'POST',
                data: {

                    statusFilter: statusFilter.val(),
                },
            },
            columns: [
                {data: 'DT_Row_Index',name: 'id'},
                ...['tid', 'cname', 'service', 'pricing', 'billing_date', 'grace_days', 'cutoff_date', 'balance', 'loyalty_points', 'status']
                .map(v => ({data: v, name: v})),
                {data: 'actions',name: 'actions',searchable: false,sortable: false}
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection