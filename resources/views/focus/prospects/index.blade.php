@extends ('core.layouts.app')

@section('title', 'Prospects Management')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Prospects Management</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-auto float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.prospects.partials.prospects-header-buttons')
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Open Prospects</div>
                                <div class="col-sm-2 col-md-1 h4 text-primary font-weight-bold">{{ $open_prospect }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-primary font-weight-bold">
                                    {{ numberFormat(div_num($open_prospect, $total_prospect) * 100) }}%</div>
                            </div>
                            <div class="row no-gutters">
                                <div class="col-sm-3 col-md-2 h4">Closed Prospects</div>
                                <div class="col-sm-2 col-md-1 h4 text-success font-weight-bold">{{ $closed_prospect }}</div>
                                <div class="col-sm-12 col-md-1 h4 text-success font-weight-bold">
                                    {{ numberFormat(div_num($closed_prospect, $total_prospect) * 100) }}%</div>
                            </div>
                        </div>
                        <div class="row ml-1 mb-3">
                            <div class="col-2">
                                <label for="client">Title</label>  
                                {{-- {{ Form::open(['route' => array('biller.prospects.destroy', 0), 'method' => 'DELETE']) }}                            --}}
                                <select name="bytitle" class="custom-select" id="bytitle" data-placeholder="Choose Title">
                                    <option value="">Choose Title</option>
                                    @foreach ($titles as $title)
                                        <option value="{{ $title->title }}">{{ $title->title }}</option>
                                    @endforeach
                                </select>
                                {{-- <div class="edit-form-btn mb-3">
                                    <label for="">&nbsp;</label>
                                    {{ Form::submit('Mass Delete', ['class' => 'form-control btn-danger mass-delete']) }}
                                </div> --}}
                                {{-- {{ Form::close() }} --}}
                            </div>
                            <div class="col-2">
                                <label for="client">Call Status</label>                             
                                <select name="bycallstatus" class="custom-select" id="bycallstatus" data-placeholder="Choose Call Status">
                                    <option value="">Choose Call Status</option>
                                    <option value="called">Called</option>
                                    <option value="calledrescheduled">Called But Rescheduled</option>
                                    <option value="callednotpicked">Called Not Picked</option>
                                    <option value="callednotavailable">Called Not Available</option>
                                    <option value="notcalled">Not Called</option>
                                    
                                </select>
                            </div>
                            
                            <div class="col-2">
                                <label for="client">Temperate Status</label>                             
                                <select name="bytemperate" class="custom-select" id="bytemperate" data-placeholder="Choose Temperate Status">
                                    <option value="">Choose Temperate Status</option>
                                    <option value="hot">Hot</option>
                                    <option value="warm">Warm</option>
                                    <option value="cold">Cold</option>
                                   
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="client">Prospect Status</label>                             
                                <select name="bystatus" class="custom-select" id="bystatus" data-placeholder="Choose Prospect Status">
                                    <option value="">Choose Prospect Status</option>
                                    <option value="open">Open</option>
                                    <option value="won">Closed-Won</option>
                                    <option value="lost">Closed-Lost</option>
                                   
                                   
                                </select>
                            </div>
                            
                        </div>
                        
                        
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <table id="prospects-table" class="table table-striped table-bordered zero-configuration"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Title</th>
                                            <th>Company Name</th>
                                            <th>Industry</th>
                                            <th>Contact Name</th>
                                            <th>Phone</th>
                                            <th>Region</th>
                                            <th>Type</th>
                                            <th>CallStatus</th>
                                            <th>Status</th>
                                            <th>Reason</th>
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
            </div>
        </div>
    </div>
    @include('focus.prospects.partials.remarks_modal')
@endsection

@section('after-scripts')
    {{ Html::script(mix('js/dataTable.js')) }}
    <script>
        const config = {
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            },
            date: {
                format: "{{ config('core.user_date_format') }}",
                autoHide: true
            },
        };

        const Index = {
            title: @json(request('bytitle')),
            temperate: @json(request('bytemperate')),
            callstatus: @json(request('bycallstatus')),
            status: @json(request('bystatus')),
            init() {
                $.ajaxSetup(config.ajax);
                this.draw_data();
                this.showModal();
                $('.mass-delete').click(this.massDelete);
                //form remark
                remark: @json(@$remark),
                $('#reminder_date').datepicker(config.date).datepicker('setDate', new Date());
                $('#bytitle').change(this.titleChange);
                $('#bytemperate').change(this.temperateChange);
                $('#bycallstatus').change(this.callStatusChange);
                $('#bystatus').change(this.statusChange);
                
            },
            massDelete() {
            event.preventDefault();
            if (!$('#bytitle').val()) return alert('Title is required!');
            const form = $(this).parents('form');
            swal({
                title: 'Are You  Sure?',
                icon: "warning",
                buttons: true,
                dangerMode: true,
                showCancelButton: true,
            }, () => form.submit());
        },
            titleChange(){
            Index.title = $(this).val();
            $('#prospects-table').DataTable().destroy();
            return Index.draw_data();
            },
            temperateChange(){
            Index.temperate = $(this).val();
            $('#prospects-table').DataTable().destroy();
            return Index.draw_data();
            },
            callStatusChange(){
            Index.callstatus = $(this).val();
            $('#prospects-table').DataTable().destroy();
            return Index.draw_data();
            },
            statusChange(){
            Index.status = $(this).val();
            $('#prospects-table').DataTable().destroy();
            return Index.draw_data();
            },
            showModal(){
                $('#prospects-table tbody').on('click','#follow', function(e) {
                 var id = $(this).attr('data-id');
                
                //show modal
                $('#remarksModal').modal('show');
                

                //varible to check if data is saved
                let saved = false;
                //set prospect id to form
                $('#prospect_id').val(id);

                $.ajax({
                    url: "{{ route('biller.prospects.followup') }}",
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function(response) {

                        $('#tableModal').append(response);
                    }
                });

                $('#save_remark').on('click', function(e) {

                    var recepient = $('#recepient').val();
                    var reminder_date = $('#reminder_date').val();
                    var remarks = $('#remarks').val();

                    //disable button
                    $("#save_remark").prop("disabled", true);
                    let formData = $('#save_remark').parents('form').serializeArray();
                    $.ajax({
                        url: "remarks",
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            saved = true;
                            $('#remarks_table').remove();
                            $('#tableModal').append(response);
                        },
                        error: function(error) {
                            console.log(error.responseText);

                        }
                    });

                    $('#recepient').val('');
                    $('#reminder_date').val('');
                    $('#remarks').val('');
                    $("#save_remark").prop("disabled", false);
                });

                $('#remarksModal').on('hidden.bs.modal', function(e) {
                    $('#remarks_table').remove();
                    $('#prospect_id').val();
                    id= "";
                    saved?window.location.reload():null;
                });
            });
            },
          

            draw_data() {
                
                $('#prospects-table').dataTable({
                    stateSave: true,
                    processing: true,
                    responsive: true,
                    language: {
                        @lang('datatable.strings')
                    },
                    ajax: {
                        url: '{{ route('biller.prospects.get') }}',
                        type: 'post',
                        data: {
                            bytitle: this.title,
                            bytemperate: this.temperate,
                            bycallstatus: this.callstatus,
                            bystatus: this.status,
                        }
                    },
                    columns: [{
                            data: 'DT_Row_Index',
                            name: 'id'
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'company',
                            name: 'company'
                        },
                        
                        {
                            data: 'industry',
                            name: 'industry'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        // {
                        //     data: 'email',
                        //     name: 'email'
                        // },
                        {
                            data: 'phone',
                            name: 'phone'
                        },
                        {
                            data: 'region',
                            name: 'region'
                        },
                       
                        
                       
                        {
                            data: 'temperate',
                            name: 'temperate'
                        },
                        {
                            data: 'call_status',
                            name: 'call_status'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'reason',
                            name: 'reason'
                        },
                        
                        {
                            data: 'actions',
                            name: 'actions',
                            searchable: false,
                            sortable: false
                        }
                    ],
                    columnDefs: [{
                        type: "custom-date-sort",
                        targets: [5]
                    }],
                    order: [
                        [0, "desc"]
                    ],
                    searchDelay: 500,
                    dom: 'Blfrtip',
                    buttons: ['csv', 'excel', 'print'],
                });
            }
        };
        $(() => Index.init());
    </script>


@endsection
