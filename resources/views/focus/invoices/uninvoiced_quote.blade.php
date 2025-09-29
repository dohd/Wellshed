@extends ('core.layouts.app')

@section ('title', 'Create Quote/PI Invoice')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Create Quote/PI Invoice</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.invoices.partials.invoices-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => 'biller.invoices.filter_invoice_quotes', 'method' => 'GET', 'id' => 'mass_add_form']) }}
                            <div class="row">                            
                                <div class="col-2">
                                    <div class="form-group pl-3" style="padding-top: .5em">
                                        {{ Form::hidden('selected_products', null, ['id' => 'selected_products']) }}
                                        {{ Form::hidden('customer', null, ['id' => 'customer']) }}
                                        {{ Form::submit('Add Selected', ['class' => 'btn btn-xs btn-success update_product_location mt-2', 'id' => 'add-selected']) }}
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label><strong>Customer :</strong></label>
                                        <select name="customer_id" id="customer_id" class="form-control" data-placeholder="Choose Customer" required>
                                            @foreach ($customers as $row)
                                                <option value="{{ $row->id }}">{{ $row->company }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label><strong>LPO :</strong></label>
                                        <select name="lpo_id" id="lpo_number" class="form-control" data-placeholder="Choose Client LPO" required>
                                            @foreach ($lpos as $row)
                                                <option value="{{ $row->id }}" customer_id="{{ $row->customer_id }}">{{ $row->lpo_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-group">
                                        <label><strong>Project :</strong></label>
                                        <select name="project_id" id="project_id" class="form-control" data-placeholder="Choose Project" required>
                                            @foreach ($projects as $row)
                                                <option value="{{ $row->id }}" customer_id="{{ $row->customer_id }}">{{ $row->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-row"></th>
                                        <th>{{ trans('customers.customer') }}</th>
                                        <th># {{ trans('quotes.quote') }} / PI</th>
                                        <th>Title</th>
                                        <th>{{ trans('general.amount') }}</th>
                                        <th>Verified</th>
                                        <th>Margin</th>
                                        <th>Quote / PI Date</th>
                                        <th>LPO No</th>
                                        <th>Project No</th>
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
</div>
@endsection

@section('after-scripts')
{{ Html::script('focus/js/select2.min.js') }}
{{ Html::script(mix('js/dataTable.js')) }}
<script>
    config = {
        select: {allowClear: true}
    };

    setTimeout(() => draw_data(), "{{ config('master.delay') }}");
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} });

    // init select2
    $('#customer_id').select2(config.select).val('').change();
    $('#lpo_number').select2(config.select).val('').change();
    $('#project_id').select2(config.select).val('').change();

    // filter records by date
    $('#search').click(function() {
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        if (start_date && end_date) {
            $('#quotesTbl').DataTable().destroy();
            return draw_data(start_date, end_date);
        }
        alert("Date range is Required");
    });

    // On filter change
    $('#customer_id, #lpo_number, #project_id').change(function() {
        var customer_id = $('#customer_id').val();
        var lpo_number = $('#lpo_number').val();
        var project_id = $('#project_id').val();

        if ($(this).is('#customer_id')) {
            $('#lpo_number option').each(function() {
                if ($(this).attr('customer_id') == customer_id) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
            $('#project_id option').each(function() {
                if ($(this).attr('customer_id') == customer_id) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
        }
        $('#customer').val(customer_id);
        $('#quotesTbl').DataTable().destroy();
        return draw_data(customer_id, lpo_number, project_id);
    });

    // on selecting a row
    const currencyState = {};
    $(document).on('click', '.row-select', function() {
        const row = $(this).parents('tr:first');
        const key = $(this).val();
        const value = row.find('.currency').attr('currency_id');
        if (this.checked) {
            const currencyIds = Object.values(currencyState);
            const last = currencyIds.slice(-1).pop();
            if (last && last != value) {
                $(this).prop('checked', false);
                alert('Select records with same currency rate!');
            } else {
                currencyState[key] = value;
            }
        } else {
            delete currencyState[key];
        }
    });

    // on multiselect
    $(document).on('click', '#select-all-row', function() {
        for (let key in currencyState) {
            if (currencyState[key]) delete currencyState[key];
        }
        const selectInputs = $(this).closest('table').find('tbody').find('input.row-select');
        if (this.checked) {
            selectInputs.each(function(i) {
                const row = $(this).parents('tr:first');
                const key = $(this).val();
                const value = row.find('.currency').attr('currency_id');
                if (i > 0) {
                    const currencyIds = Object.values(currencyState);
                    const last = currencyIds.slice(-1).pop();
                    if (last && last != value) {
                        $(this).prop('checked', false);
                    } else {
                        $(this).prop('checked', true);
                        currencyState[key] = value;
                    }
                } else {
                    $(this).prop('checked', true);
                    currencyState[key] = value;
                }
            });
        } else {
            selectInputs.each(function() {
                $(this).prop('checked', false);
            });
        }   
    });


    // selected row state
    const selectedRowState = {};
    $(document).on('change', '.row-select', function() {
        const row = $(this).parents('tr');
        if ($(this).prop('checked')) selectedRowState['_'+row.index()] = $(this).val();
        else delete selectedRowState[row.index()];
    });

    // submit selected rows
    $(document).on('click', '#add-selected', function(e) {
        e.preventDefault();
        if (!$('#customer_id').val() && $('.row-select:checked').length > 1) 
            return swal('Filter records by customer');

        const selected_rows = Object.values(selectedRowState);
        if (!selected_rows.length) {
            $('#selected_products').val('');
            return swal('No records Selected');
        }
        $('input#selected_products').val(selected_rows);

        swal({
            title: 'Are You  Sure?',
            icon: "warning",
            buttons: true,
            dangerMode: true,
            showCancelButton: true,
        }, () => $('form#mass_add_form').submit()); 
    });

    // draw dataTable
    function draw_data(customer_id = '', lpo_number = '', project_id = '') {
        const table = $('#quotesTbl').dataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.invoices.get_uninvoiced_quote') }}",
                type: 'POST',
                data: { customer_id, lpo_number, project_id },
            },
            columns: [
                {
                    data: 'mass_select',
                    searchable: false,
                    sortable: false
                },
                ...[
                    'customer', 'tid', 'title', 'total', 'verified_total', 'diff_total', 
                    'created_at', 'lpo_number', 'project_tid'
                ].map(v => ({data: v, name: v})),
            ],
            order:[[0, 'desc']],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print',]
        });
    }
</script>
@endsection