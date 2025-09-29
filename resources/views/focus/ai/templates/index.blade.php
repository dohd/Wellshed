@extends ('core.layouts.app')
@section ('title', 'Message Templates')

@section('content')
    <div class="content-wrapper">
        <div class="content-header row mb-1">
            <div class="content-header-left col-6">
                <h4 class="content-header-title">Message Templates</h4>
            </div>
            <div class="content-header-right col-6">
                <div class="media width-250 float-right mr-3">
                    <div class="media-body media-right text-right">
                        @include('focus.whatsapp.partials.template-header-buttons')
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
                                <table id="mediaBlocksTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Template Name</th>
                                            <th>Status</th>
                                            <th>Category</th>
                                            <th>Action</th>
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
    @include('focus.media_blocks.partials.view-modal')
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
<script>
    const config = {
        ajax: { 
            headers: { 
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Authorization': "Bearer {{ $business->whatsapp_access_token }}",
            } 
        },
        date: {format: "{{ config('core.user_date_format') }}", autoHide: true},
        spinner: '<div class="text-center"><span class="font-large-1"><i class="fa fa-spinner spinner"></i></span></div>',
    };
    
    const Index = {
        business: @json($business),

        init() {
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date);

            $(document).on('click', '.view', Index.viewRecord);
            $(document).on('click', '.delete', Index.deleteRecord);

            Index.drawData();
        },

        viewRecord() {
            $('.modal-body').html(config.spinner);
            $.post("{{ route('api.media_blocks.show') }}", {
                'template_id': $(this).attr('template-id'),
            })
            .then(data => {
                if (data.template) {
                    const {template} = data;
                    $('.modal-body').html(`
                        <h4 class="text-center"><b>${template.name}</b></h4>
                        <p class="text-center">${template.text}</p>
                    `);
                }
                if (data.message) {
                    $('.modal-body').html(`<p class="text-danger">${data.message}</p>`);
                }
            })
            .fail((xhr, status, err) => {
                if (err.message) {
                    $('.modal-body').html(`<p class="text-danger">${err.message}</p>`);
                }
            })
        },

        deleteRecord() {
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this record!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((isConfirm) => {
                if (isConfirm) {
                    const business = Index.business;
                    const url = `${business.graph_api_url}/${business.whatsapp_business_account_id}/message_templates`;
                    $.ajax({
                        url, 
                        type: 'DELETE',
                        data: {
                            hsm_id: $(this).attr('template-id'),
                            name: $(this).attr('template-name'),
                        },
                        success: function(response) {
                            $('#mediaBlocksTbl').DataTable().destroy();
                            Index.drawData();
                        },
                        error: function(xhr, status, error) {
                            console.error('Error deleting resource:', error);
                        }
                    });
                }
            });
        },

        drawData() {
            $('#mediaBlocksTbl tbody tr').remove();
            // fetch media blocks
            const business = Index.business;
            const url = `${business.graph_api_url}/${business.whatsapp_business_account_id}/message_templates`;
            $.get(url)
            .then(({data}) => {
                if (data.length) {
                    data.forEach((v,i) => {
                        $('#mediaBlocksTbl tbody').append(`
                            <tr>
                                <td>${i+1}</td>
                                <td class="font-weight-bold">${v.name}</td>
                                <td><span class="badge ${v.status? 'badge-success' : 'badge-secondary'}">${v.status? 'Approved' : 'Rejected'}</span></td>
                                <td>${v.category}</td>
                                <td>
                                    <!--<a href="#" class="btn btn-primary round view" template-id="${v.id}" data-toggle="modal" data-target="#viewModal" title="View"><i  class="fa fa-eye"></i></a>-->
                                    <a href="#" class="btn btn-danger round delete" template-id="${v.id}" template-name="${v.name}" data-toggle="tooltip" data-placement="top" title="Delete"><i  class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        `);                        
                    }) 

                    $('#mediaBlocksTbl').dataTable({
                        stateSave: true,
                        responsive: true,
                        dom: 'Blfrtip',
                        buttons: ['csv', 'excel', 'print'],
                    });
                }
            })
            .fail((xhr,status,err) => {
                console.log(err)
            })
        },
    };
    
    $(Index.init);
</script>
@endsection
