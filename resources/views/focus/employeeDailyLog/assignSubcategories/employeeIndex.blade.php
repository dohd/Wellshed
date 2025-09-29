@extends ('core.layouts.app')

@section ('title', 'Employee Key Performance Indicators Allocations')


@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Employee Key Performance Indicators Allocations</h2>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.employeeDailyLog.partials.edl-header-buttons')
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

                            <table id="etc-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Customer</th>
                                        <th>Branch</th>
                                        <th>Allocations (KPI | Frequency | Key Activity | Target | UoM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="100%" class="text-center text-success font-large-1"><i class="fa fa-spinner spinner"></i></td>
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
        select2: {allowClear: true},
    }
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#etc-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.edl-subcategory-allocations.allocations") }}',
                type: 'GET',
                data: { c_type: 0 }
            },
            columns: [
                {
                    data: 'employee',
                    name: 'employee',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'allocations',
                    name: 'allocations',
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
        });
    }

    $('#employeeList').select2(config.select2);
</script>
@endsection