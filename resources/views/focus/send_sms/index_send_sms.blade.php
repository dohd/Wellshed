@extends ('core.layouts.app')

@section ('title', 'Manage Sent SMS')

@section('page-header')
    <h1>{{ 'Manage Sent SMS For All Tenants' }}</h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class="content-header-title mb-0">{{ 'Manage Sent SMS For All Tenants' }}</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.send_sms.partials.send_sms-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="card" id="filters">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-3">
                                    <label for="client">Customer</label>                             
                                    <select name="client_id" class="custom-select" id="customer" data-placeholder="Choose Client">
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ strtoupper($company->cname) }}</option>
                                        @endforeach
                                    </select>
                                </div>
        
                                <div class="col-2">
                                    <label for="status">Sms Status</label>
                                    <select name="status" id="status" class="custom-select">
                                        <option value="">-- select status --</option>
                                        @foreach (['sent', 'not_sent'] as $status)
                                            <option value="{{ $status }}">{{  ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                    
                                <div class="col-2">
                                    <div class="mb-2">Total Cost</div>                           
                                    <div class="good-worth">0.00</div>
                                </div>
                 
                            </div> 
                            
                                <div class="row mt-3">
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
                                    
                                </div>
                                              
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    <table id="send_sms-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
                                            <th>MessageType</th>
                                            <th>Delivery Type</th>
                                            <th>SMS Status</th>
                                            <th>Date/Time to Send</th>
                                            <th>Cost</th>
                                            {{-- <th>{{ trans('labels.general.actions') }}</th> --}}
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
    {{ Html::script('focus/js/select2.min.js') }}
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>

        const config = {
            date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
            datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
        };
        const Index = {
            start_date: '',
            end_date: '',
            
            init(){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('.datepicker').datepicker(config.datepicker).datepicker('setDate', new Date());
                $('.datepicker').change(Index.dateChange);
                $('#status').change(Index.statusChange);
                $('#search').click(Index.filterCriteriaChange);
                $('#customer').select2({allowClear: true}).val('').trigger('change')
                .change(Index.customerChange);
                
                Index.drawDataTable();
            },

            filterCriteriaChange() {

                $('#send_sms-table').DataTable().destroy();
                return Index.drawDataTable({
                });   
            },
            customerChange() {
                $('#send_sms-table').DataTable().destroy();
                return Index.drawDataTable();
            },
            statusChange() {
                $('#send_sms-table').DataTable().destroy();
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
                $('#send_sms-table').dataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    stateSave: true,
                    language: {@lang('datatable.strings')},
                    ajax: {
                        url: '{{ route("biller.send_sms.get_all_sms") }}',
                        type: 'post',
                        data: {
                            customer_id: $('#customer').val(),
                            status: $('#status').val(),
                            start_date: this.start_date, 
                            end_date: this.end_date,
                            ...params,
                        },
                        dataSrc: ({data}) => {
                            $('.good-worth').text('0.00');
                            if (data.length && data[0].aggregate) {
                                const aggr = data[0].aggregate;
                                console.log(aggr);
                                $('.good-worth').text(aggr.good_worth);
                            }
                            return data;
                        },
                    },
                columns: [
                    {data: 'DT_Row_Index', name: 'id'},
                    {data: 'subject', name: 'subject'},
                    {data: 'message_type', name: 'message_type'},
                    {data: 'delivery_type', name: 'delivery_type'},
                    {data: 'status', name: 'status'},
                    {data: 'sent_at', name: 'sent_at'},
                    {data: 'total_cost', name: 'total_cost'}
                    // {data: 'actions', name: 'actions', searchable: false, sortable: false}
                ],
                    order: [[0, "desc"]],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print']
                });
            },
        };
        $(Index.init)
    </script>
@endsection