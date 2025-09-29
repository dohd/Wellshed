@extends ('core.layouts.app',['page'=>'class="horizontal-layout horizontal-menu content-detached-left-sidebar app-contacts " data-open="click" data-menu="horizontal-menu" data-col="content-detached-left-sidebar"'])

@section ('title', ' Job Schedule | Job Card  Management')

@section('page-header')
    <h1>
        {{ trans('labels.backend.customers.management') }}
        <small>{{ trans('labels.backend.customers.create') }}</small>
    </h1>
@endsection
@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-detached content-right">
                <div class="content-body">
                    <div class="content-overlay"></div>


                    <section class="row all-contacts">
                        <div class="col-12">
                            <div class="card">

                                <div class="card-content">
                                    <div class="card-body">
                                        <!-- Task List table -->
                                        <div class="card-body">

                                            @yield('customer_view')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="sidebar-detached sidebar-left">
                <div class="sidebar">
                    <div class="bug-list-sidebar-content">
                        <!-- Predefined Views -->
                        <div class="card">
                          

                            <div class="card-body">
                                <p class="lead"> Details</p>
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <span class="badge badge-primary badge-pill float-right">{{numberFormat($jobschedule->equipments->count('id'))}}</span>
                                        <a href="javascript:void()">
                                            Equipments</a>
                                    </li>
                                    <li class="list-group-item">
                                        <span class="badge bg-purple badge-pill float-right">{{numberFormat($jobschedule->totalserviced->count('id'))}}</span>
                                        <a href="javascript:void()">
                                            Serviced</a>
                                    </li>

                                    <li class="list-group-item">
                                        <span class="badge bg-purple badge-pill float-right">{{numberFormat($jobschedule->equipments->count('id')-$jobschedule->totalserviced->count('id'))}}</span>
                                        <a href="javascript:void()">
                                            UnServiced</a>
                                    </li>

                                </ul>
                            </div>
                            <!--/ Groups-->

                            <!-- contacts view -->
                            <div class="card-body border-top-blue-grey border-top-lighten-5">
                                <div class="list-group">

                                    <a href="{{route('biller.projectequipments.create')}}?rel_type=1&rel_id={{$jobschedule->id}}"
                                       class="list-group-item list-group-item-action "><i
                                                class="fa fa-plug"></i> Load Machines</a> 
                                </div>
                            </div>

                            <!-- Groups-->


                        </div>
                        <!--/ Predefined Views -->

                    </div>
                </div>
            </div>
        </div>
    </div>
     @include('focus.jobschedules.modal.add_jobcard')


@endsection

@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}

    <script>
        




   
            $("#submit-data_jobcard").on("click", function (e) {
                e.preventDefault();
                var form_data = [];
                form_data['form'] = $("#data_form_jobcard").serialize();
                form_data['url'] = $('#action-url_jobcard').val();
                $('#AddProjectModal').modal('toggle');
                addObject(form_data, true);
            });

          $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#jobcard-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                aaSorting: [[3, 'asc']],
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.projectequipments.get") }}',
                    type: 'post',
                    data: function ( d ) {
                        d.region_id = $('#region_id').val();
                        d.branch_id = $('#branch_id').val();
                        d.section_id = $('#section_id').val();
                        d.rel_id={{ $jobschedule->id }};
                        d.rel_type=1;
                        d.job_card=1;
                        d = __datatable_ajax_callback(d);
                    }
                },
                columns: [
                    { data: 'mass_delete' ,searchable: false, sortable: false },


                    {data: 'region', name: 'region'},
                     {data: 'branch', name: 'branch'},
                    {data: 'section', name: 'section'},
                    {data: 'location', name: 'location'},
                    {data: 'equip_serial', name: 'equip_serial'},
                    {data: 'manufacturer', name: 'manufacturer'},
                    {data: 'model', name: 'model'},
                    
                    {data: 'category', name: 'category'},
                    {data: 'related_equipments', name: 'related_equipments'},
                    {data: 'status', name: 'status'},
                 
                     
                    {data: 'created_at', name: '{{config('module.branches.table')}}.created_at'},
                    
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {extend: 'csv', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'excel', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'print', footer: true, exportOptions: {columns: [0, 1]}}
                    ]
                }
            });
            $('#Jobcard-table_wrapper').removeClass('form-inline');

    

        //This method removes unwanted get parameter from the data.
  function __datatable_ajax_callback(data){
    for (var i = 0, len = data.columns.length; i < len; i++) {
        if (! data.columns[i].search.value) delete data.columns[i].search;
        if (data.columns[i].searchable === true) delete data.columns[i].searchable;
        if (data.columns[i].orderable === true) delete data.columns[i].orderable;
        if (data.columns[i].data === data.columns[i].name) delete data.columns[i].name;
    }
    delete data.search.regex;

    return data;
}



   var dataTable = $('#serviced-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                aaSorting: [[3, 'asc']],
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.projectequipments.get") }}',
                    type: 'post',
                    data: function ( d ) {
                        d.region_id = $('#region_id').val();
                        d.branch_id = $('#branch_id').val();
                        d.section_id = $('#section_id').val();
                        d.rel_id={{ $jobschedule->id }};
                        d.rel_type=1;
                        d.job_card=2;
                        d = __datatable_ajax_callback(d);
                    }
                },
                columns: [
                   


                    {data: 'region', name: 'region'},
                     {data: 'branch', name: 'branch'},
                    {data: 'section', name: 'section'},
                    {data: 'location', name: 'location'},
                    {data: 'equip_serial', name: 'equip_serial'},
                    {data: 'manufacturer', name: 'manufacturer'},
                    {data: 'model', name: 'model'},
                    
                    {data: 'job_card', name: 'job_card'},
                    {data: 'technician', name: 'technician'},
                    {data: 'servicedate', name: 'servicedate'},
                     {data: 'recommendation', name: 'recommendation'},
                 
                     
                    {data: 'created_at', name: '{{config('module.branches.table')}}.created_at'},
                    
                ],
                order: [[0, "asc"]],
                searchDelay: 500,
                dom: 'Blfrtip',
                buttons: {
                    buttons: [

                        {extend: 'csv', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'excel', footer: true, exportOptions: {columns: [0, 1]}},
                        {extend: 'print', footer: true, exportOptions: {columns: [0, 1]}}
                    ]
                }
            });
            $('#serviced-table_wrapper').removeClass('form-inline');

$(document).on('click', '#select-all-row', function(e) {
    if (this.checked) {
        $(this)
            .closest('table')
            .find('tbody')
            .find('input.row-select')
            .each(function() {
                if (!this.checked) {
                    $(this)
                        .prop('checked', true)
                        .change();
                }
            });
    } else {
        $(this)
            .closest('table')
            .find('tbody')
            .find('input.row-select')
            .each(function() {
                if (this.checked) {
                    $(this)
                        .prop('checked', false)
                        .change();
                }
            });
    }
});

$(document).on('change', '#region_id, #branch_id, #section_id', 
                function() {
                    $('#jobcard-table').DataTable().ajax.reload();
                  // dataTable.ajax.reload();
            });


 $(document).on('click', '#add-selected', function(e){
                e.preventDefault();
                var selected_rows = getSelectedRows();
                
                if(selected_rows.length > 0){
                    $('input#selected_products').val(selected_rows);

                    console.log(selected_rows);
                    swal({
                        title: 'Are You  Sure?',
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_add_form').submit();
                        }
                    });
                } else{
                    $('input#selected_products').val('');
                    swal('No record Selected');
                }    
            });

         function getSelectedRows() {
            var selected_rows = [];
            var i = 0;
            $('.row-select:checked').each(function () {
                selected_rows[i++] = $(this).val();
            });

            return selected_rows; 
        }

            $(document).on('click', '.update_product_location', function(e){
            e.preventDefault();
            var selected_rows = getSelectedRows();
              $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
             $('.from_date').datepicker('setDate', 'today');
            $('.from_date').datepicker({autoHide: true, format: '{{date(config('core.user_date_format'))}}'});
            
            if(selected_rows.length > 0){
                $('input#selected_products').val(selected_rows);
                var type = $(this).data('type');
                var modal = $('#edit_product_location_modal');
                if(type == 'add') {
                    modal.find('.remove_from_location_title').addClass('hide');
                    modal.find('.add_to_location_title').removeClass('hide');
                } else if(type == 'remove') {
                    modal.find('.add_to_location_title').addClass('hide');
                    modal.find('.remove_from_location_title').removeClass('hide');
                }

                modal.modal('show');
                //modal.find('#product_location').select2({ dropdownParent: modal });
                //modal.find('#product_location').val('').change();
                ////modal.find('#update_type').val(type);
                modal.find('#selected_eqipment_id').val(selected_rows);
            } else{
                $('input#selected_products').val('');
                swal('No Record Selected');
            }    
        });
   
    </script>
@endsection