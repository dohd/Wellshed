@extends ('core.layouts.app')

@section ('title', 'Client PO Management')

@section('content')
<div class="content-wrapper">
    <div class="alert alert-warning alert-dismissible d-none lpo-alert">
        <strong>Forbidden!</strong><span class="lpoMsg"></span>
    </div> 
    <div class="content-header row">        
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h4 class="content-header-title">Client Purchase Order Management</h4>
        </div>
        <div class="content-header-right col">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.lpo.partials.lpo-header-buttons')
                </div>
            </div>
        </div>
    </div>
    <div class="content-body">
        <div class="card todo-details rounded-0">
            <div class="sidebar-toggle d-block d-lg-none info"><i class="ft-menu font-large-1"></i></div>
            <div class="search"></div>
            <div class="card-body">
                <table id="lpo-table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client & Branch</th>
                            <th>#LPO No</th>
                            <th>LPO Amount</th>
                            <th>Invoiced (QT/PI)</th>
                            <th>Verified & Uninvoiced (QT/PI)</th>
                            <th>Approved & Unverified (QT/PI)</th>
                            <th>Balance</th>
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
@include('focus.lpo.partials.edit-lpo')
@include('focus.lpo.partials.create-lpo')
@endsection

@section('after-scripts')
{{ Html::script(mix('js/dataTable.js')) }}
{{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
{{ Html::script('focus/js/select2.min.js') }}
<script>
    const config = {
        ajaxSetup: { headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"} },
        datepicker: {format: "{{ config('core.user_date_format') }}", autoHide: true}
    };

    $.ajaxSetup(config.ajaxSetup);
    setTimeout(() => draw_data(), "{{ config('master.delay') }}");

    // Delete LPO
    $(document).on('click', 'a.delete-lpo', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        swal(
            {
                title: 'Are you sure to delete this ?',
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#DD6B55'
            }, 
            function() {
                $.ajax({ url })
                .done(() => location.reload())
                .fail((err) => {
                    if (err.status === 403) {
                        $('.lpo-alert').removeClass('d-none');
                        $('.lpoMsg').text(err.responseJSON.message);
                        setTimeout(() => $('.lpo-alert').addClass('d-none'), 5000);
                    }
                })            
            }
        );     
    });
    
    // Fetch update data
    $(document).on('click', 'a.update-lpo', function(e) {
        e.preventDefault();
        const id = $(this).attr('href');

        $.ajax({ url: baseurl + `lpo/${id}/edit`, })
        .done(function(data) { 
            const {customer, branch, lpo} = data;
            const formId = '#updateLpoForm ';
            $(formId+'#lpo_id').val(lpo.id);
            $(formId+'#person').append(new Option(customer.name, customer.id, 'selected', true));
            $(formId+'#branch_id').append(new Option(branch.name, branch.id, 'selected', true));

            $(formId+'#lpo_no').val(lpo.lpo_no);
            $(formId+'#amount').val(accounting.formatNumber(lpo.amount));
            $(formId+'#remark').val(lpo.remark);

            setTimeout(() => {
                $(formId+'#date').datepicker('setDate', new Date(lpo.date));;
            }, 500);
        });
    });
    
    // On modal open
    $(document).on('shown.bs.modal', '#updateLpoModal, #AddLpoModal', function() {
        const modal = $(this);

        // init datepicker
        modal.find('.datepicker')
        .datepicker(config.datepicker)
        .datepicker('setDate', new Date());

        // initialize customer select2
        const person = modal.find("#person");
        person.select2({
            dropdownParent: modal,
            ajax: {
                url: "{{ route('biller.customers.select') }}",
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: function(params) { 
                    return { search: params.term }
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: `${item.name} - ${item.company}`,
                                id: item.id
                            }
                        })
                    };
                },
            }
        });

        // on selecting customer fetch branches
        const branch =  modal.find("#branch_id");
        person.on('change', function() {
            branch.html('').select2({
                dropdownParent: modal,
                ajax: {
                    url: "{{ route('biller.branches.select') }}",
                    type: 'POST',
                    quietMillis: 50,
                    data: ({term}) => ({search: term, customer_id: person.val()}),
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });
        }); 

        // LPO amount change
        const amount =  modal.find("#amount");
        amount.change(function() {
            const el = $(this);
            const value = accounting.unformat(el.val());
            el.val(accounting.formatNumber(value));
        });
    });
    
    // fetch table data
    function draw_data() {
        var dataTable = $('#lpo-table').dataTable({
            processing: true,
            responsive: true,
            stateSave: true,
            language: {@lang('datatable.strings')},
            ajax: {
                url: "{{ route('biller.lpo.get') }}",
                type: 'post',
            },
            columns: [{
                    data: 'DT_Row_Index',
                    name: 'id'
                },
                {
                    data: 'customer',
                    name: 'customer'
                },
                {
                    data: 'lpo_no',
                    name: 'lpo_no'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'invoiced',
                    name: 'invoiced'
                },
                {
                    data: 'verified_uninvoiced',
                    name: 'verified_uninvoiced'
                },
                {
                    data: 'approved_unverified',
                    name: 'approved_unverified'
                },
                {
                    data: 'balance',
                    name: 'balance'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false
                }
            ],
            columnDefs: [
                { type: "custom-number-sort", targets: [3, 7] },
            ],
            order: [[0, "desc"]],
            searchDelay: 500,
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'print']
        });
    }
</script>
@endsection