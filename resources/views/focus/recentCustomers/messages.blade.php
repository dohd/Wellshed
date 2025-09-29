@extends ('core.layouts.app')

@section ('title', 'Recent Customer & Prospect Communications')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h2 class=" mb-0">Recent Customer & Prospect Communications </h2>
        </div>

        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">

                    <div class="btn-group" role="group" aria-label="Basic example">

                        <a href="{{ route( 'biller.recent-customer-messages' ) }}" class="btn btn-info  btn-lighten-2 round"><i
                                    class="fa fa-list-alt"></i> {{trans( 'general.list' )}}</a>

                    </div>

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



                            <ul class="nav nav-tabs" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1" role="tab"
                                       aria-selected="true" style="font-size: 20px;">Sent Emails</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="base-tab9" data-toggle="tab" aria-controls="tab9" href="#tab9" role="tab"
                                       aria-selected="false" style="font-size: 20px;">Sent Sms</a>
                                </li>

                            </ul>

                            <div class="tab-content px-1 pt-1">

                                <!---Email tab-->
                                <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">

                                    <h1>Emails</h1>

                                    <div class="row mb-2 mt-2">

                                        <div class="col-9 col-lg-4">
                                            <label for="email_type_filter">Filter by Type</label>
                                            <select class="form-control box-size mb-2 email-filter filter" id="email_type_filter" required data-placeholder="Filter by Type" aria-label="Filter by Type">
                                                <option value="">Filter by Type</option>
                                                @foreach (['Prospect', 'Customer'] as $t)
                                                    <option value="{{ $t }}">
                                                        {{ $t }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-9 col-lg-4">
                                            <label for="email_customer_filter">Filter by Customer</label>
                                            <select class="form-control box-size mb-2 email-filter filter" id="email_customer_filter" required data-placeholder="Filter by Customer" aria-label="Filter by Customer">
                                                <option value="">Filter by Customer</option>
                                                @foreach ($customers as $c)
                                                    <option value="{{ $c['id'] }}">
                                                        {{ $c['company'] }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>

                                        <div class="col-3">
                                            <button id="clearEmailFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                        </div>

                                    </div>

                                    <table id="emailTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>Customer/Prospect</th>
                                            <th>Date</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Content</th>
                                            <th>Sender</th>
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



                                {{-- Sms tab --}}
                                <div class="tab-pane" id="tab9" role="tabpanel" aria-labelledby="base-tab9">

                                    <h1>Sms</h1>

                                    <div class="row mb-2 mt-2">

                                        <div class="col-9 col-lg-4">
                                            <label for="sms_type_filter">Filter by Type</label>
                                            <select class="form-control box-size mb-2 sms-filter filter" id="sms_type_filter" required data-placeholder="Filter by Type" aria-label="Filter by Type">
                                                <option value="">Filter by Type</option>
                                                @foreach (['Prospect', 'Customer'] as $t)
                                                    <option value="{{ $t }}">
                                                        {{ $t }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-9 col-lg-4">
                                            <label for="sms_customer_filter">Filter by Customer</label>
                                            <select class="form-control box-size mb-2 sms-filter filter" id="sms_customer_filter" required data-placeholder="Filter by Customer" aria-label="Filter by Customer">
                                                <option value="">Filter by Customer</option>
                                                @foreach ($customers as $c)
                                                    <option value="{{ $c['id'] }}">
                                                        {{ $c['company'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-3">
                                            <button id="clearSmsFilters" class="btn btn-secondary round mt-2" > Clear Filters </button>
                                        </div>

                                    </div>

                                    <table id="smsTable" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Phone</th>
                                            <th>Content</th>
                                            <th>Sender</th>
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
    </div>
</div>
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('focus/js/select2.min.js') }}

<script>
    setTimeout(() => {
            drawEmailTable();
            drawSmsTable();
        }
        , "{{ config('master.delay') }}"
    );

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    const emailTypeFilter = $('#email_type_filter');
    const smsTypeFilter = $('#sms_type_filter');
    const emailCustomerFilter = $('#email_customer_filter');
    const smsCustomerFilter = $('#sms_customer_filter');

    $('.filter').select2({ allowClear: true });

    emailCustomerFilter.change(() => {
        emailTypeFilter.val('').trigger('change'); // Clear and trigger change on customer filter
    });

    $('.email-filter').change(() => {
        $('#emailTable').DataTable().destroy();
        drawEmailTable();
    });


    //SMS FILTERS
    smsCustomerFilter.change(() => {
        smsTypeFilter.val('').trigger('change'); // Clear and trigger change on customer filter
    });

    $('.sms-filter').change(() => {
        $('#smsTable').DataTable().destroy();
        drawSmsTable();
    });


    const clearEmailFilters = $('#clearEmailFilters');
    const clearSmsFilters = $('#clearSmsFilters');


    clearEmailFilters.click(() => {

        emailCustomerFilter.val('').trigger('change');
        emailTypeFilter.val('').trigger('change');

        $('#emailTable').DataTable().destroy();
        drawEmailTable();
    })

    clearSmsFilters.click(() => {

        smsCustomerFilter.val('').trigger('change');
        smsTypeFilter.val('').trigger('change');

        $('#smsTable').DataTable().destroy();
        drawSmsTable();
    })


    function drawEmailTable() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#emailTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.recent-customer-email-table") }}',
                type: 'GET',
                data: {

                    emailCustomerFilter: emailCustomerFilter.val(),
                    emailTypeFilter: emailTypeFilter.val(),
                }
            },
            columns: [
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'subject',
                    name: 'subject'
                },
                {
                    data: 'content',
                    name: 'content'
                },
                {
                    data: 'sender',
                    name: 'sender'
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [1, "desc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }


    function drawSmsTable() {
        const tableLan = {@lang('datatable.strings')};
        var dataTable = $('#smsTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            language: tableLan,
            ajax: {
                url: '{{ route("biller.recent-customer-sms-table") }}',
                type: 'GET',
                data: {

                    smsCustomerFilter: smsCustomerFilter.val(),
                    smsTypeFilter: smsTypeFilter.val(),
                }
            },
            columns: [
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'content',
                    name: 'content'
                },
                {
                    data: 'sender',
                    name: 'sender'
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false
                }
            ],
            order: [
                [1, "desc"]
            ],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print'],
        });
    }


</script>
@endsection