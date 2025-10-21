@extends ('core.layouts.app')
@section ('title', 'User Managment')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12">
            <h4 class="content-header-title mb-0">User Managment</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.hrms.partials.hrms-header-buttons')
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

                            <div class="row mb-2">
                                <div class="col-9 col-lg-3">
                                    <label for="purchaseOrderMonth" >Filter by Active Status </label>
                                    <select class="form-control box-size filter" id="employeeStatus" name="employeeStatus" data-placeholder="Filter by Employee Status">

                                        @foreach (['Active' => 1, 'Deactivated' => 0] as $key => $value)
                                            <option value="{{ $value }}">
                                                {{ $key }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>
                            </div>

                            <table id="hrms-table"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                <thead>
                                    <tr>
                                        <th>NO#</th>
                                        <th>NAME</th>
                                        <th>ROLE</th>
                                        <th>EMAIL</th>
                                        <th>STATUS</th>
                                        <th>CUSTOMER</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center text-success font-large-1">
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    const employeeStatusFilter = $('#employeeStatus');
    employeeStatusFilter.select2({ allowClear: true });
    $('.filter').change(() => {
        console.table({
            employeeStatusFilter: employeeStatusFilter.val(),
        });
        $('#hrms-table').DataTable().destroy();
        draw_data();
    })
    const clearFilters = $('#clearFilters');
    clearFilters.click(() => {
        employeeStatusFilter.val(1).trigger('change');
        $('#hrms-table').DataTable().destroy();
        draw_data();
    })
    $(document).on('click', ".user_active", function (e) {
        var cid = $(this).attr('data-cid');
        var active = $(this).attr('data-active');
        if (active == 1) {
            $(this).removeClass('checked');
            $(this).attr('data-active', 0);
        } else {
            $(this).addClass('checked');
            $(this).attr('data-active', 1);
        }
        $.ajax({
            url: '{{ route("biller.hrms.active") }}',
            type: 'post',
            data: {'cid': cid, 'active': active}
        });
    });
    function draw_data() {
        console.table({
            employeeStatusFilter: employeeStatusFilter.val(),
        });
        $('#hrms-table').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: '{{ route("biller.hrms.get") }}',
                type: 'post',
                data: {
                    @if(request('rel_type')>0)
                    rel_type:{{request('rel_type')}},
                    rel_id:{{request('rel_id',0)}},
                    @endif
                    employeeStatusFilter: employeeStatusFilter.val(),
                }
            },
            columns: [
                {data: 'DT_Row_Index', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'role', name: 'role'},
                {data: 'email', name: 'email'},
                {data: 'active', name: 'active'},
                {data: 'customer', name: 'customer'},                    
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print']
        });
    }
</script>
@endsection
