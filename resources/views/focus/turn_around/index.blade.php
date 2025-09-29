@extends('core.layouts.app')

@php
    $query_str = request()->getQueryString();
    $quote_label = "Turn Around Time";
    if ($query_str == 'page=pi') $quote_label = 'Proforma Invoice Management';
@endphp

@section ('title', $quote_label)

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">{{ $quote_label }}</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.quotes.partials.quotes-header-buttons')
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card" id="filters">
            <div class="card-content">
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col-4">
                            <label for="client">Customer</label>                             
                            <select name="client_id" class="custom-select" id="client" data-placeholder="Choose Client">
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->company }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="filter">Filter Criteria</label>                             
                            @php
                                $criteria = [
                                    'Unapproved', 'Approved & Uninvoiced', 'Approved & Unbudgeted', 'Budgeted & Unverified', 'Verified with LPO & Uninvoiced',
                                    'Verified without LPO & Uninvoiced', 'Approved without LPO & Uninvoiced', 'Invoiced & Due',
                                    'Invoiced & Partially Paid', 'Invoiced & Paid', 'Cancelled'
                                ];
                            @endphp
                            <select name="filter" class="custom-select" id="status_filter">
                                <option value="">-- Choose Filter Criteria --</option>
                                @foreach ($criteria as $val)
                                    <option value="{{ $val }}">{{ $val }}</option>
                                @endforeach
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
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-content">
                <div class="card-body">  
                    <table id="quotesTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer - Branch</th>
                                <th>Ticket No</th>
                                <th>Ticket Date</th>   
                                <th>DJC No</th>
                                <th>DJC Date</th>
                                <th>{{ $query_str == 'page=pi' ? '#PI' : '#Quote'  }} No</th>
                                <th>Quote Date</th>    
                                <th>Approval Date</th> 
                                <th>Project No.</th> 
                                <th>Project Date</th> 
                                <th>Verification Date</th>
                                <th>RJC No</th>
                                <th>RJC Date</th>
                                <th>Invoice No</th>
                                <th>Invoice Date</th>
                               <th>Payment No</th>
                               <th>Payment Date</th>
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
        ajaxSetup: {headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }},
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    const Index = {
        customers: @json($customers),

        init(config) {
            $.ajaxSetup(config.ajaxSetup);
            $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
            $('#client').select2({allowClear: true}).val('').trigger('change');

            $('#filters').on('change', '#status_filter, #client', this.filterCriteriaChange);
            this.drawDataTable();
            $('#search').click(this.searchDateClick);
        },

        filterCriteriaChange() {
            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable({
                status_filter: $('#status_filter').val(),
                client_id: $('#client').val()
            });   
        },
        searchDateClick() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            if (!startDate || !endDate) return alert("Date range required!"); 

            $('#quotesTbl').DataTable().destroy();
            return Index.drawDataTable({
                start_date: startDate, 
                end_date: endDate
            });
        },

        drawDataTable(params={}) {
            $('#quotesTbl').dataTable({
                processing: true,
                responsive: true,
                stateSave: true,
                language: {@lang('datatable.strings')},
                ajax: {
                    url: "{{ route('biller.turn_around.search') }}",
                    type: 'POST',
                    data: {
                        ...params,
                        page: location.href.includes('page=pi') ? 'pi' : 'qt'
                    },
                    dataSrc: ({data}) => {
                        $('#amount_total').val('');
                        if (data.length) $('#amount_total').val(data[0].sum_total);                            
                        return data;
                    },
                },
                columns: [{
                        data: 'DT_Row_Index',
                        name: 'id'
                    },
                    ...[
                        'customer','lead_tid', 'lead_date','djcs_tid','djcs_date','tid','date', 
                         'approved_date','project_no','project_date','project_closure_date','rjcs','rjcs_date',  'invoice_tid', 'invoice_date','payment_tid','payment_date'
                    ].map(v => ({data: v, name: v}))
                    
                ],
                columnDefs: [
                    { type: "custom-number-sort", targets: 5 },
                    { type: "custom-date-sort", targets: [3,5,7,8,10,11,13,15,17] }
                ],
                order:[[0, 'desc']],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    };

    $(() => Index.init(config));
</script>
@endsection