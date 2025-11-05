@extends ('core.layouts.app')

@section('title', 'Orders Management')

@section('page-header')
    <h1>{{ 'Orders Management' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Orders Management' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.customer_orders.partials.customer_orders-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="customer" class="form-label mb-1">Customer</label>
                                        <select name="customer" id="customer" class="form-control"
                                            data-placeholder="Search Customer">
                                            <option value="">Search Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->company ?: $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="status" class="form-label mb-1">status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="">Search status</option>
                                            @foreach (['draft', 'confirmed', 'started', 'completed', 'cancelled', 'suspended'] as $i => $val)
                                                <option value="{{ $val }}">
                                                    {{ ucfirst($val) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="card-content">

                                <div class="card-body">
                                    <table id="customer_orders-table"
                                        class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                        width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Order No.</th>
                                                <th>Customer</th>
                                                <th>Order Type</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                                <th>{{ trans('labels.general.actions') }}</th>
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
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        $("#customer").select2({
            allowClear: true
        });

        let dataTable;

        $(function() {
            setTimeout(function() {
                draw_data()
            }, {{ config('master.delay') }});

            // Refresh table when filters change
            $("#customer, #status").on("change", function() {
                dataTable.draw();
            });
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            dataTable = $('#customer_orders-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route('biller.customer_orders.get') }}',
                    type: 'post',
                    data: function(d) {
                        d.customer = $('#customer').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    {
                        data: 'tid',
                        name: 'tid'
                    },
                    {
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'order_type',
                        name: 'order_type'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'total',
                        name: 'total'
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
                buttons: {
                    buttons: [{
                            extend: 'csv',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'excel',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        },
                        {
                            extend: 'print',
                            footer: true,
                            exportOptions: {
                                columns: [0, 1]
                            }
                        }
                    ]
                }
            });

            $('#customer_orders-table_wrapper').removeClass('form-inline');
        }
    </script>

@endsection
