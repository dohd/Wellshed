@extends ('core.layouts.app')

@section('title', 'Labour Allocation Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Labour Allocation Management</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                <a class="btn btn-success" href="{{ route('biller.labour_allocations.show', $labour->project_id) }}">View</a>
                @include('focus.labour_allocations.partials.labour_allocation-header-buttons')
                
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="col-4">
                            <p>{{ $employee }}</p>
                        </div>
                        <button type="button" class="btn btn-info float-right mr-2" id="addemployee" data-toggle="modal"
                                data-target="#AddLabourModal">
                                <i class="fa fa-plus-circle"></i> Attach
                        </button>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <table id="labour_allocationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Hrs</th>
                                        <th>Type</th>
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
    @include('focus.labour_allocations.partials.attach-labour')
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
    };

    const Index = {
        labourItems: @json($labour_items),
        id: @json($id),
        init() {
            this.drawDataTable();
        },

        drawDataTable() {
            $('#labour_allocationsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.labour_allocations.get_employee_items') }}",
                    type: 'POST',
                    data: {id: "{{ $id }}"},
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'date', name: 'date'},
                    {data: 'hrs', name: 'hrs'},
                    {data: 'type', name: 'type'},
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init());
</script>
@endsection
