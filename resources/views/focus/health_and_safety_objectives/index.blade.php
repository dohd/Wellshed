@extends ('core.layouts.app')

@section('title', 'Health and Safety Objectives')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title mb-0">Health and Safety Objectives</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right">
                    <div class="media-body media-right text-right">
                        @include('focus.health_and_safety_objectives.partials.health-and-safety-objectives-header-buttons')
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
                                <table id="healthAndSafetyObjectivesTable"
                                    class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        };

        const Index = {
            date: @json(request('date')),
            pdca_cycle: @json(request('pdca_cycle')),

            init() {
                this.drawDataTable();
                // $('#date').val(this.dateId).change(this.dateChange);
                // $('#pdca_cycle').val(this.pdcaCycleId).change(this.pdcaCycleChange);
            },

            // pdcaCycleChange() {
            //     Index.pdcaCycleId = $(this).val();
            //     $('#healthAndSafetyTable').DataTable().destroy();
            //     return Index.drawDataTable();
            // },

            // dateChange() {
            //     Index.dateId = $(this).val();
            //     $('#healthAndSafetyTable').DataTable().destroy();
            //     return Index.drawDataTable();
            // },

            drawDataTable() {
                $('#healthAndSafetyObjectivesTable').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.health-safety-objectives.get') }}',
                        // data: {
                        //     date: this.dateId,
                        //     pdca_cycle: this.pdcaCycleId,
                        // },
                        type: 'post'
                    },

                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            searchable: false,
                            sortable: false
                        }
                    ],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            },
        };

        $(() => Index.init());
    </script>
@endsection
