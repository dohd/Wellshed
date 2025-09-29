@extends ('core.layouts.app')

@section ('title', 'Load Equipments')

@section('page-header')
    <h1>Load Equipment </h1>
@endsection

@section('content')
    <div class="">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <h4 class=" mb-0">Load Equipment</h4>

                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="media width-250 float-right">

                        <div class="media-body media-right text-right">
                            @include('focus.equipments.partials.equipments-header-buttons')
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <div class="row">
    <div class="col-md-12">
            <div class="card">
    <div class="box  box-primary " id="accordion">
  <div class="box-header with-border" style="cursor: pointer;">
    <h3 class="box-title">
      <a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
         <i class="fa fa-filter" aria-hidden="true"></i>  Filters
      </a>
    </h3>
  </div>
  <div id="collapseFilter" class="panel-collapse active collapse  in " aria-expanded="true">
    <div class="box-body">

           <div class="row">
                    <div class="col-sm-4">
                        <div class='form-group'>
                            {{ Form::label( 'name', 'Region',['class' => 'col-lg-2 control-label']) }}
                            <div class='col-md-12'>
                                <select class="form-control select-box col-12" name="region" id="region_id">
                                    <option value="">All Regions</option>
                                    
                                    @foreach($region as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class='form-group'>
                            {{ Form::label( 'phone', 'Branch',['class' => 'col-lg-6 control-label']) }}
                            <div class='col-md-12'>
                                <select class="form-control select-box col-12" name="branch" id="branch_id">
                                    <option value="">All Branches</option>
                                    
                                    @foreach($branch as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                      <div class="col-sm-4">
                        <div class='form-group'>
                            {{ Form::label( 'phone', 'Section',['class' => 'col-lg-6 control-label']) }}
                            <div class='col-md-12'>
                                  <select class="form-control select-box col-12" name="section" id="section_id">
                                    <option value="">All Sections</option>
                                    
                                    @foreach($section as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>



     
    </div>
  </div>
</div>    </div>
</div>
</div>


                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-content">

                                <div class="card-body">
                                    <table id="equipments-table"
                                           class="table table-striped table-bordered zero-configuration" cellspacing="0"
                                           width="100%">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-row"></th>
                                          
                                           
                                            <th>Client</th>
                                            <th>Region</th>
                                            <th>Branch</th>
                                            <th>Section</th>
                                             <th>Location</th>
                                            <th>Serial</th>
                                            <th>Manufacturer</th>
                                            <th>Model</th>
                                            <th>Category</th>
                                            <th>Related</th>
                                            <th>Status </th>
                                            
                                            <th>{{ trans('general.createdat') }}</th>
                                         
                                        </tr>
                                        </thead>


                                        <tbody>
                                        <tr>
                                            <td colspan="100%" class="text-center text-success font-large-1"><i
                                                        class="fa fa-spinner spinner"></i>
                                                    </td>
                                        </tr>
                                        </tbody>

                                             <tfoot>
            <tr>
                <td colspan="13">
                <div style="display: flex; width: 100%;">
                 
                      {{ Form::open(['route' => 'biller.projectequipments.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'mass_add_form']) }}
                     {!! Form::hidden('selected_products', null, ['id' => 'selected_products']); !!}
                     {!! Form::hidden('shedule_id', $jobschedule->id, ['id' => 'shedule_id']); !!}
                     {!! Form::hidden('project_id', $jobschedule->project_id, ['id' => 'project_id']); !!}
                     {!! Form::hidden('client_id', $jobschedule->client_id, ['id' => 'client_id']); !!}
                    {!! Form::submit('Add Selected', array('class' => 'btn btn-xs btn-primary', 'id' => 'add-selected')) !!}    
                       {{ Form::close() }}
                
                  
                    &nbsp;
                     {{ Form::open(['route' => 'biller.projectequipments.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'mass_deactivate_form']) }}
                       
                     {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']); !!}
                        {!! Form::submit('Remove Selected', array('class' => 'btn btn-xs btn-danger', 'id' => 'delete-selected')) !!}

                  {{ Form::close() }}
                   
                  
                    </div>
                </td>
            </tr>
        </tfoot>
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
    {{ Html::script(mix('js/dataTable.js')) }}

    {{ Html::script('core/app-assets/vendors/js/extensions/sweetalert.min.js') }}
   {{ Html::script('focus/js/select2.min.js') }}

    <script>
        $(function () {
            setTimeout(function () {
                draw_data()
            }, {{config('master.delay')}});
        });

        function draw_data() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#equipments-table').dataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                aaSorting: [[3, 'asc']],
                language: {
                    @lang('datatable.strings')
                },
                ajax: {
                    url: '{{ route("biller.equipments.get") }}',
                    type: 'post',
                    data: function ( d ) {
                        d.region_id = $('#region_id').val();
                        d.branch_id = $('#branch_id').val();
                        d.section_id = $('#section_id').val();
                        d.rel_id={{ $jobschedule->client_id }};
                        d.rel_type=1;
                        d = __datatable_ajax_callback(d);
                    }
                },
                columns: [
                    { data: 'mass_delete' ,searchable: false, sortable: false },
                    {data: 'customer', name: 'customer'},
                    {data: 'region', name: 'region'},
                    {data: 'branch', name: 'branch'},
                    {data: 'section', name: 'section'},
                    {data: 'location', name: 'location'},
                    {data: 'equip_serial', name: 'equip_serial'},
                    {data: 'manufacturer', name: 'manufacturer'},
                    {data: 'model', name: 'model'},
                    
                    {data: 'category', name: 'category'},
                    {data: 'relationship', name: 'relationship'},
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
            $('#Equipments-table_wrapper').removeClass('form-inline');

        }

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
                    $('#equipments-table').DataTable().ajax.reload();
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
    </script>
@endsection
