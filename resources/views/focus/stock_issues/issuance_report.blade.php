@extends ('core.layouts.app')

@section('title', 'Stock Movement Report')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Movement Report</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_issues.partials.stockissue-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <label for="customer">Products</label>
                                <select name="product_id" id="product" class="form-control" data-placeholder="Choose Product">
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <form action="{{route('biller.stock_issues.print_stock_movement')}}" method="POST">
                        <div class="row mt-3">
                                @csrf
                                <div class="col-2">{{ trans('general.search_date')}}</div>
                                @php
                                    $now = date('d-m-Y');
                                    $start = date('d-m-Y', strtotime("{$now} - 1 months"));
                                @endphp
                                <div class="col-2">
                                    <input type="text" name="start_date" value="{{ $start }}" id="start_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-2">
                                    <input type="text" name="end_date" value="{{ $now }}" id="end_date" class="form-control form-control-sm datepicker">
                                </div>
                                <div class="col-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm">
                                </div>
                                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-print"></i></button>
                                
                            </div>
                        </form>
                        <hr>  
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table id="stock-issue-tbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                {{-- <th>Date</th> --}}
                                <th>Product Name</th>
                                <th>Product Code</th>
                                <th>Issue Qty</th>
                                <th>Return Qty</th>
                                <th>Stock Adj Qty</th>
                                <th>UoM</th>                                                     
                                <th>Warehouse</th>                                                     
                                {{-- <th>{{ trans('labels.general.actions') }}</th> --}}
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
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    const config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        start_date: '',
        end_date: '',

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('.datepicker').change(this.dateChange);
            $('#product').select2({allowClear: true}).val('').trigger('change')
            .change(this.productChange);
            $('#search').click(this.filterCriteriaChange);
            Index.drawDataTable();
        },

        filterCriteriaChange() {

            $('#stock-issue-tbl').DataTable().destroy();
            return Index.drawDataTable({});   
        },


        productChange() {
            $('#stock-issue-tbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        dateChange() {
            let start = $('#start_date').val();
            let end = $('#end_date').val();
            if (start && end) {
                Index.start_date = start;
                Index.end_date = end;
            } else {
                Index.start_date = '';
                Index.end_date = '';
            }
        },

        drawDataTable(params = {}) {
            $('#stock-issue-tbl').dataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.stock_issues.products_movement_items') }}",
                    type: 'POST',
                    data: {
                        product_id: $('#product').val(),
                        start_date: this.start_date, 
                        end_date: this.end_date,
                        ...params,
                    },
                },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                ...['name','code', 'issue_qty', 'return_qty','stock_adj_qty','unit', 'warehouse', ].map(v => ({data: v, name: v}))
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                pageLength: 10,
            });
        }
        
    };

    $(() => Index.init(config));
</script>
@endsection
