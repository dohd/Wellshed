@extends ('core.layouts.app')

@section ('title', 'Valuation Quotes/PI')

@section('content')
<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Valuation Quotes/PI</h4>
        </div>   
        <div class="content-header-right col-6">
            <div class="media width-250 float-right mr-3">
                <div class="media-body media-right text-right">
                    @include('focus.job_valuations.partials.jobvaluation-header-buttons')
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
                            <div class="row">
                                <div class="col-2">
                                    <select class="custom-select" id="customer" data-placeholder="Search Customer">
                                        <option value=""></option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                        @endforeach
                                    </select>
                                </div>   
                                <div class="col-2">
                                    <select id="status" class="custom-select">
                                        <option value="">-- Valuation Status--</option>
                                        <option value="pending">Pending</option>
                                        <option value="partial">Partial</option>
                                        <option value="complete">Complete</option>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-2">{{ trans('general.search_date')}} </div>
                                <div class="col-2">
                                    <input type="text" name="start_date" id="start_date" class="form-control datepicker date30  form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="text" name="end_date" id="end_date" class="form-control datepicker form-control-sm" autocomplete="off" />
                                </div>
                                <div class="col-2">
                                    <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                </div>
                            </div>
                            <hr>
                            <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>#Quote/PI</th>
                                        <th>Customer - Branch</th>
                                        <th>Title</th>                                            
                                        <th>Subtotal</th>
                                        <th>% Valued</th>
                                        <th>Amt Valued</th>
                                        <th>Balance</th>
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
                            {{-- form redirect to valuation creation --}}
                            <form action="{{ route('biller.job_valuations.create') }}">
                                <input type="hidden" name="quote_id" id="quote">
                            </form>  
                        </div>
                    </div>
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
        ajaxSetup: {
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
        },
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        init() {
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#customer').select2({allowClear: true});

            $('#status, #customer').change(Index.filterChange);

            $('#quotesTbl').on('change', '.select-row', Index.selectRow);
            $('#search').click(Index.searchDateClick);
            Index.drawDataTable();
        },

        filterChange() {
            $('#quotesTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        searchDateClick() {
            Index.startDate = $('#start_date').val();
            Index.endDate = $('#end_date').val();
            if (!Index.startDate || !Index.endDate) return alert("Date range required!"); 

            $('#quotesTbl').DataTable().destroy();
            Index.drawDataTable();
        },

        selectRow() {
            const el = $(this);
            if (el.prop('checked')) {
                $('#quote').val(el.val());
                $('#quotesTbl tbody tr').each(function() {
                    if ($(this).find('.select-row').val() != el.val()) {
                        $(this).find('.select-row').prop('checked', false);
                    }
                });
            } else {
                $('#quote').val('');
                $('#quotesTbl tbody tr').each(function() {
                    $(this).find('.select-row').prop('checked', false);
                });
            }
            if ($('#quote').val()) {
                swal({
                    title: 'Valuate this item?',
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    showCancelButton: true,
                }, () => $('form').submit()); 
            }
        },

        drawDataTable() {
            $('#quotesTbl').dataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: '{{ route("biller.job_valuations.get_quotes") }}',
                    type: 'POST',
                    data: {
                        start_date: Index.startDate,
                        end_date: Index.endDate,
                        status: $('#status').val(),
                        customer_id: $('#customer').val(),
                    },
                },
                columns: [
                    {data: 'checkbox',  searchable: false,  sortable: false},
                    ...[
                        'tid', 'customer', 'notes', 'subtotal', 'perc_valuated', 'valuated', 'balance', 
                    ].map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: [ 'csv', 'excel', 'print'],
            });
        },
    };

    $(Index.init());
</script>
@endsection