@extends ('core.layouts.app')

@section('title', 'BoQ Valuations')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">BoQ Valuations</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.boq_valuations.partials.boq_valuations-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2">
                            <select class="custom-select" id="customer" data-placeholder="Search Customer">
                                <option value=""></option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                @endforeach
                            </select>
                        </div>                        
                    </div>
                    <hr>

                    <table id="valuationsTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>To Invoice</th>
                                <th>#Serial</th>
                                <th>#BoQ No</th>
                                <th>Customer</th>
                                <th>Valuation Title</th>
                                <th>BoQ Total</th>
                                <th>Valuation</th>  
                                <th>Balance</th>                                                  
                                <th>Date</th>
                                <th>#Invoice No.</th>
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
    <a class="d-none" id='inv-redirect'></a>
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
        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);
            $('#customer').select2({allowClear: true});

            $('#customer').on('change', Index.filterChange);
            $('#valuationsTbl').on('change', '.row-check', Index.checkboxChange);
            Index.drawDataTable();
        },

        filterChange() {
            $('#valuationsTbl').DataTable().destroy();
            return Index.drawDataTable();
        },

        checkboxChange() {
            if ($(this).prop('checked')) {
                $('#inv-redirect').attr('href', $(this).attr('href'));
                setTimeout(() => $('#inv-redirect')[0].click(), 500);                
            }
        },

        drawDataTable() {
            $('#valuationsTbl').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.boq_valuations.get') }}",
                    type: 'POST',
                    data: {
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                        customer_id: $('#customer').val(),                    
                    },
                },
                columns: [
                    // {data: 'DT_Row_Index', name: 'id'},
                    ...['row_check', 'tid', 'boq_tid', 'customer','note', 'quote_amount', 'subtotal', 'balance', 'date', 'invoice_tid'].map(v => ({data: v, name: v})),
                    {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(Index.init);
</script>
@endsection
