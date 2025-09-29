@extends ('core.layouts.app')

@section ('title', trans('labels.backend.hrms.management'))

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12">
            <h4 class="content-header-title">{{ trans('labels.backend.access.roles.management') }}</h4>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.hrms.partials.role-header-buttons')
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
                            <table id="roles-table"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                <thead>
                                <tr>
                                    <th>{{ trans('labels.backend.access.roles.table.role') }}</th>
                                    <th>{{ trans('labels.backend.access.roles.table.permissions') }}</th>
                                    <th>{{ trans('labels.general.actions') }}</th>
                                </tr>
                                </thead>
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

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    $('#roles-table').dataTable({
        statesave: true,
        processing: true,
        serverside: true,
        language: {@lang('datatable.strings')},
        ajax: {
            url: '{{ route("biller.role.get") }}',
            type: 'post',
        },
        columns: [
            {data: 'name', name: '{{config('access.roles_table')}}.name'},
            {data: 'permissions', name: '{{config('access.permissions_table')}}.display_name', sortable: false},
            {data: 'actions', name: 'actions', searchable: false, sortable: false}
        ],
        order: [[0, "asc"]],
        searchDelay: 500,
        dom: 'lBfrtip',
        buttons: ['csv', 'excel', 'print'],
    });

    $(document).ready(function() {
        // Use event delegation to handle the click event for dynamically added elements
        $(document).on('click', '.trash-role', function(e) {

            e.preventDefault();
            let buttonId = $(this).attr('id');
            let confirmAction = confirm("Are you sure you want to delete this Role? This action cannot be reversed past this point!");
            if (confirmAction) {
                // Use the button ID in the URL
                window.location.href = '/role/' + buttonId + '/trash';
            }
        });
    });

</script>
@endsection

