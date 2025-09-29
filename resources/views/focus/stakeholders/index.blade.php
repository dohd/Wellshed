@extends ('core.layouts.app')

@section ('title', 'Company Stakeholders')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Company Stakeholders </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.stakeholders.header-buttons')
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
                                <div class="col-9 col-lg-4">
                                    <label for="employee" >Filter by Authorizers</label>
                                    <select class="form-control box-size filter" id="authorizer" name="authorizer" data-placeholder="Filter by Authorizer">

                                        <option value=""></option>
                                        @foreach ($authorizers as $au)
                                            <option value="{{ $au->id }}">
                                                {{ $au->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-9 col-lg-4">
                                    <label for="employee" >Filter by Company</label>
                                    <select class="form-control box-size filter" id="company" name="company" data-placeholder="Filter by Filter by Company">

                                        <option value=""></option>
                                        @foreach ($companies as $co)
                                            <option value="{{ $co }}">
                                                {{ $co }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-3">
                                    <button id="clearFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                </div>
                            </div>

                            <table id="pcTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Company</th>
                                        <th>Designation</th>
                                        <th>Access Reason</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Authorizer</th>
                                        <th>Access Start</th>
                                        <th>Access End</th>
                                        <th>Actions</th>
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
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    const authorizerFilter = $('#authorizer');
    const companyFilter = $('#company');

    $('.filter').select2({ allowClear: true });

    $('.filter').change(() => {

        $('#pcTable').DataTable().destroy();
        draw_data();
    })

    const clearFilters = $('#clearFilters');


    clearFilters.click(() => {

        authorizerFilter.val('').trigger('change');
        companyFilter.val('').trigger('change');

        $('#pcTable').DataTable().destroy();
        draw_data();
    })


    function draw_data() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#pcTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.stakeholders.index") }}',
                type: 'GET',
                data: {
                    authorizerFilter: authorizerFilter.val(),
                    companyFilter: companyFilter.val(),
                }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'sh_company',
                    name: 'sh_company'
                },
                {
                    data: 'sh_designation',
                    name: 'sh_designation'
                },
                {
                    data: 'sh_access_reason',
                    name: 'sh_access_reason'
                },
                {
                    data: 'sh_primary_contact',
                    name: 'sh_primary_contact'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'sh_authorizer',
                    name: 'sh_authorizer'
                },
                {
                    data: 'sh_access_start',
                    name: 'sh_access_start'
                },
                {
                    data: 'sh_access_end',
                    name: 'sh_access_end'
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