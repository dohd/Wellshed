@extends ('core.layouts.app')

@section('title', 'Approved Budget Prints ')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h2 class=" mb-0">Approved Budget Prints </h2>
            </div>

        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">

                                <div class="row mb-2">
                                    <div class="col-9 col-lg-6">
                                        <label for="purchaseOrderMonth">Filter by Customer</label>
                                        <select class="form-control box-size filter" id="customerFilter"
                                            data-placeholder="Filter by Customer">

                                            <option value="">Filter by Customer</option>
                                            @foreach ($customers as $c)
                                                <option value="{{ $c->id }}">
                                                    {{ $c->company }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <button id="clearFilters" class="btn btn-secondary round mt-2"> Clear Filters
                                        </button>
                                    </div>
                                </div>

                                <table id="pcTable" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Quote</th>
                                            <th>Title</th>
                                            <th>Project</th>
                                            <th>Customer</th>
                                            <th>Branch</th>
                                            <th>Created By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i
                                                    class="fa fa-spinner spinner"></i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('focus.quoteBudgets.modal.send_link')
        </div>
    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}

    <script>
        setTimeout(() => draw_data(), "{{ config('master.delay') }}");

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });

        const customerFilter = $('#customerFilter');

        customerFilter.select2({
            allowClear: true
        });
        $('#store_users').select2({
            allowClear: true
        });
        $('#technicians').select2({
            allowClear: true
        });

        $('.filter').change(() => {

            console.table({
                customerFilter: customerFilter.val(),
            });

            $('#pcTable').DataTable().destroy();
            draw_data();
        })

        const clearFilters = $('#clearFilters');

        clearFilters.click(() => {

            customerFilter.val('').trigger('change');

            $('#pcTable').DataTable().destroy();
            draw_data();
        })

        $('#pcTable').on('click', '.click', function(e) {
            var data = e.target.getAttribute('quote_id');
            $('#quote').val(data);
            console.log(data);
        });

        function draw_data() {
            const tableLan = {
                @lang('datatable.strings')
            };
            var dataTable = $('#pcTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: tableLan,
                ajax: {
                    url: '{{ route('biller.quotes-approved-budgets') }}',
                    type: 'GET',
                    data: {
                        customerFilter: customerFilter.val(),
                    }
                },
                columns: [{
                        data: 'quote',
                        name: 'quote'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'project',
                        name: 'project'
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
                        data: 'created_by',
                        name: 'created_by'
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
    </script>
@endsection


