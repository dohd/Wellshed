@extends ('core.layouts.app')

@section ('title', 'Employee Key Performance Indicators Allocations')


@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Employee Key Performance Indicators Allocations</h2>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">


                            <div class="row">


                                {{ Form::open(['route' => 'biller.edl-subcategory-allocations.allocate', 'method' => 'GET', 'id' => 'edl-subcategory-allocations.create']) }}
                                <div class="col-12 form-group row">

                                    <div class="col-8 col-lg-8">
                                        <label for="employee">Employee:</label>
                                        <select class="form-control box-size select2" id="employeeList" name="employee" required>
                                            <option value="">-- Select Employee --</option>
                                            @foreach ($employees as $emp)
                                                <option value="{{ $emp['id'] }}">
                                                    {{ $emp['employee_name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-4 edit-form-btn mt-2">
{{--                                        {{ link_to_route('biller.edl-subcategory-allocations.index', trans('buttons.general.cancel'), [], ['class' => 'btn btn-secondary btn-md mr-1 round']) }}--}}
                                        {{ Form::submit('Assign Key Performance Indicators', ['class' => 'btn btn-primary btn-md round']) }}
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                {{ Form::close() }}

                            </div>


                            <table id="etc-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Customer</th>
                                        <th>Branch</th>
                                        <th>Allocations (KPI | Frequency | Key Activity | Target | UoM)</th>
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
                url: '{{ route("biller.edl-subcategory-allocations.index") }}',
                type: 'GET',
                data: { c_type: 0 }
            },
            columns: [
                {
                    data: 'employee',
                    name: 'employee'
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
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
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