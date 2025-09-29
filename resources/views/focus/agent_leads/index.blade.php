@php use App\Models\tenant\Tenant; @endphp
@extends ('core.layouts.app')

@section ('title', 'AI Leads Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">AI Leads Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        <div class="btn-group" role="group" aria-label="Basic example">
                            <a href="{{ route('biller.agent_leads.omni_transcripts') }}" class="btn btn-info  btn-lighten-2">
                                <i class="fa fa-list-alt"></i> Transcripts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">    
                            <div class="row">
                                <div class="col-md-2">
                                    <select class="custom-select" id="source-filter">
                                        <option value="">-- Lead Source --</option>
                                        @foreach (['whatsapp', 'facebook', 'instagram', 'website'] as $item)
                                            <option value="{{ $item }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select class="custom-select" id="location" data-placeholder="Search Location">
                                        <option value=""></option>
                                        @foreach ($locations as $key => $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select class="custom-select" id="product_brand" data-placeholder="Search Brand">
                                        <option value=""></option>
                                        @foreach ($productBrands as $key => $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select class="custom-select" id="quoteStatus">
                                        <option value="">-- Filter Quote Status --</option>
                                        <option value="quoted">Quoted</option>
                                        <option value="none">N/Quoted</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <!-- Open Ticket and delete buttons -->
                                @php
                                    $tenant = Tenant::find(\Illuminate\Support\Facades\Auth::user()->ins);
                                    $package = optional(optional(optional($tenant)->package)->service)->package;
                                    $packageNo = '';
                                    if ($package) $packageNo = optional($package->first())->package_number;
                                @endphp
                                @if($packageNo != 'PKG-6704cf05b5591')
                                    <span class="badge badge-pill badge-danger delete-ticket d-none" style="cursor: pointer"><i class="fa fa-trash fa-lg"></i> Delete</span>
                                @endif
                                <span class="badge badge-pill badge-success open-ticket d-none" style="cursor: pointer"><i class="ft-phone-outgoing"></i> Open Ticket</span>
                                <!-- Date filters  -->            
                                <div class="row mb-1 mt-1">
                                    <div class="col-md-2" style="max-width: 200px">{{ trans('general.search_date')}} </div>
                                    <div class="col-md-1">
                                        <input type="text" value="{{ date('d-m-Y') }}" id="start_date" class="date30 form-control form-control-sm datepicker">
                                    </div>
                                    <div class="col-md-1">
                                        <input type="text" value="{{ date('d-m-Y') }}" id="end_date" class="form-control form-control-sm datepicker">
                                    </div>
                                    <div class="col-md-1">
                                        <input type="button" name="search" id="search" value="Search" class="btn btn-info btn-sm" />
                                    </div>
                                </div>
                                <hr>
                                <table id="leads-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="check-all" autocomplete="off"></th>
                                            <th>Client Name</th>
                                            <th>Phone No.</th>
                                            <th>Email</th>
                                            <th>Project</th>
                                            <th>Location</th>
                                            <th>Product Brand</th>
                                            <th>Product Spec</th>
                                            <th>Created At</th>
                                            <th>Quote Status</th>
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
    <a class="d-none create-lead"></a>
    {{ Form::open(['route' => ['biller.agent_leads.destroy', @$agent_lead->id ?: 1], 'method' => 'POST', 'id' => 'delete-form']) }}
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="checked_ids" id="checked-ids">
    {{ Form::close() }}
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
        const config = {
            ajax: { headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" } },
            date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        };
        
        setTimeout(() => draw_data(), "{{ config('master.delay') }}");
        $.ajaxSetup(config.ajax);
        $('.datepicker').datepicker(config.date);
        $('#location, #product_brand').select2({allowClear: true});
        
        // on action buttons click
        $('.open-ticket').click(function () {
            $('.create-lead')[0].click();
        });
        $('.delete-ticket').click(function () {
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => $('#delete-form').submit());
        });

        // on row check
        const checkedIds = [];
        $('#leads-table').on('change', '.row-check', function () {
            const dataId = $(this).attr('data-id');
            if ($(this).prop('checked')) {
                checkedIds.push(dataId);
            } else {
                const indx = checkedIds.indexOf(dataId);
                if (indx > -1) checkedIds.splice(indx, 1);
            }

            if (checkedIds.length == 1) {
                const createLeadUrl = "{{ route('biller.leads.create') }}?agent_lead_id=" + checkedIds[0];
                $('.create-lead').attr('href', createLeadUrl);
                $('.open-ticket, .delete-ticket').removeClass('d-none');
            } else if (checkedIds.length > 1) {
                $('.create-lead').attr('href', '');
                $('.open-ticket').addClass('d-none');
                $('.delete-ticket').removeClass('d-none');
            } else {
                $('.create-lead').attr('href', '');
                $('.open-ticket, .delete-ticket').addClass('d-none');
            }
            $('#checked-ids').val(checkedIds.join(','));
        });
        $('#check-all').change(function () {
            const checkedIds2 = [];
            if ($(this).prop('checked')) {
                if ($('.row-check:not(:disabled)').length) {
                    $('.open-ticket').addClass('d-none');
                    $('.delete-ticket').removeClass('d-none');
                }
                $('.row-check:not(:disabled)').each(function () {
                    const dataId = $(this).attr('data-id');
                    checkedIds2.push(dataId);
                    $(this).prop('checked', true);
                });
            } else {
                if ($('.row-check:not(:disabled)').length) {
                    $('.open-ticket, .delete-ticket').addClass('d-none');
                }
                $('.row-check:not(:disabled)').prop('checked', false);
            }
            $('#checked-ids').val(checkedIds2.join(','));
        });

        let initDate = false;
        $('#search').click(function() {
            initDate = true;
            $('#leads-table').DataTable().destroy();
            return draw_data();
        });

        $(document).on('change', '#source-filter, #location, #product_brand', function() {
            $('#leads-table').DataTable().destroy();
            return draw_data();
        });

        function draw_data() {
            $('#leads-table').dataTable({
                stateSave: true,
                processing: true,
                responsive: true,
                language: {@lang("datatable.strings")},
                ajax: {
                    url: '{{ route("biller.agent_leads.get") }}',
                    type: 'POST',
                    data: {
                        start_date: initDate? $('#start_date').val() : '',
                        end_date: initDate? $('#end_date').val() : '',
                        location: $('#location').val(),
                        product_brand: $('#product_brand').val(),
                        user_type: $('#source-filter').val(),
                        quote_status: $('#quoteStatus').val(),
                    },
                },
                columns: [
                    {
                        data: 'row_check',
                        name: 'row_check',
                        searchable: false,
                        sortable: false,
                    },
                    ...[
                        'client_name', 'phone_no', 'email', 'project', 'location', 
                        'product_brand', 'product_spec', 'created_at', 'quote_status',
                    ]
                    .map(v => ({data: v, name: v})),
                ],
                order: [[0, "desc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: ['csv', 'excel', 'print'],
            });
        }
    </script>
@endsection
