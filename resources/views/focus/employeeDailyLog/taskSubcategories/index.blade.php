@extends ('core.layouts.app')

@section ('title', 'Employee Log Key Activities')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Employee Log Key Activities </h2>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            <div class="">
                                <div class="media float-right">
                                    <a href="{{ route('biller.employee-task-subcategories.create') }}" class="btn btn-primary"> Create EDL Key Activity </a>
                                </div>
                            </div>


                            <table id="etc-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Frequency</th>
                                        <th>Department</th>
                                        <th>Key Activity</th>
                                        <th>Target</th>
                                        <th>UoM</th>
                                        <th>Action</th>
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
<script>
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
                url: '{{ route("biller.employee-task-subcategories.index") }}',
                type: 'GET',
                data: { c_type: 0 }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'frequency',
                    name: 'frequency'
                },
                {
                    data: 'department',
                    name: 'department'
                },
                {
                    data: 'key_activity',
                    name: 'key_activity'
                },
                {
                    data: 'target',
                    name: 'target'
                },
                {
                    data: 'uom',
                    name: 'uom'
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [0, "asc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }
</script>
@endsection