@extends('core.layouts.app')

@section('title', 'Non-Project Class Report')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-2">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h4 class="content-header-title mb-0">Non-Project Class Report</h4>

            </div>
            <div class="content-header-right col-md-6 col-12">
                <div class="media width-250 float-right">

                    <div class="media-body media-right text-right">
                        @include('focus.purchase_classes.partials.header-buttons')
                    </div>
                </div>
            </div>
        </div>
        <div class="card">

            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="base-tab1" data-toggle="tab" aria-controls="tab1" href="#tab1"
                           role="tab" aria-selected="true"><span class="">Purchases </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="base-tab2" data-toggle="tab" aria-controls="tab2" href="#tab2"
                           role="tab" aria-selected="false"><span>Purchase Orders</span>
                        </a>
                    </li>

                </ul>
                <div class="tab-content px-1 pt-1">
                    <div class="tab-pane active" id="tab1" role="tabpanel" aria-labelledby="base-tab1">
                        @include('focus.purchase_classes.partials.purchases-report')
                    </div>
                    <div class="tab-pane" id="tab2" role="tabpanel" aria-labelledby="base-tab2">
                        @include('focus.purchase_classes.partials.purchase-orders-report')
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>

        $(function () {
            setTimeout(function () {
                drawDataTables()
            }, {{config('master.delay')}});
        });

        function drawDataTables(){

            $('#purchasesTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.purchase_classes.get-purchases-data", $purchaseClass->id) }}',
                    type: 'post',
                },
                columns: [
                    { data: 'p_number', name: 'po_number' },
                    { data: 'supplier', name: 'supplier' },
                    { data: 'date', name: 'date' },
                    { data: 'project', name: 'project' },
                    { data: 'budget_line', name: 'budget_line' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'total', name: 'total' },
                    // Add other columns as needed
                ],
                order: [[1, 'asc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });

            $('#purchaseOrdersTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.purchase_classes.get-purchase-orders-data", $purchaseClass->id) }}',
                    type: 'post',
                },
                columns: [
                    { data: 'po_number', name: 'po_number' },
                    { data: 'supplier', name: 'supplier' },
                    { data: 'date', name: 'date' },
                    { data: 'project', name: 'project' },
                    { data: 'budget_line', name: 'budget_line' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'total', name: 'total' },
                ],
                order: [[1, 'asc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                pageLength: -1,
            });

        }


    </script>
@endsection
