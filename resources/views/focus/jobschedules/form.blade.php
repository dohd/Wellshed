<div class='row'>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'customer_id', 'Customer',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select id="person" name="client_id" class="form-control round required select-box"  data-placeholder="{{trans('customers.customer')}}" >
                     
                                </select></div>
        </div>
    </div>
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'project_id', 'Project',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select id="project" name="project_id" class="form-control   select-box"  data-placeholder="Branch" >
                     
                                </select>
            </div>
        </div>
    </div>

    
</div>


<div class='row'>
	  <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'region_id', 'Regions',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select id="region" name="region_id[]" class="form-control round required select-box"  data-placeholder="Regions" multiple>
                     
                                </select></div>
        </div>
    </div>
   
    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'status', 'Status',['class' => 'col-12 control-label']) }}
            <div class="col">
                <select class="custom-select" id="status" name="status">
                <option value="Not Started">Not Started</option>
                <option value="Continuing">Continuing</option>
                <option value="Paused">Paused</option>
                <option value="Terminated">Terminated</option>
                <option value="Competed">Competed</option>
               
            </select>
            </div>
        </div>
    </div>


 
</div>



<div class='row'>


   <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'expected_start_date', ' Start Date',['class' => 'col control-label']) }}
            <div class='col-12'>
                <fieldset class="form-group position-relative has-icon-left">
                    <input type="text" class="form-control round required"
                           placeholder="Start Date*"    name="expected_start_date"
                           data-toggle="datepicker" required="required">
                    <div class="form-control-position">
                      <span class="fa fa-calendar"
                            aria-hidden="true"></span>
                    </div>

                </fieldset>
            </div>
        </div>
    </div>

     <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'main_duration', 'Maintenance Duration:',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('duration', null, ['class' => 'col form-control ', 'placeholder' => 'Duration (In days)','required'=>'required']) }}
            </div>
        </div>
    </div>



</div>


<div class='row'>
     



    <div class='col-md-6'>
        <div class='form-group'>
            {{ Form::label( 'note', 'Note:',['class' => 'col-12 control-label']) }}
            <div class="col">
                {{ Form::text('note', null, ['class' => 'col form-control ', 'placeholder' => 'Note*','required'=>'required']) }}</div>
        </div>
    </div>



    
</div>



@section('after-scripts')
    {{-- For DataTables --}}
    {{ Html::script(mix('js/dataTable.js')) }}
    {{ Html::script('core/app-assets/vendors/js/extensions/moment.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/fullcalendar.min.js') }}
    {{ Html::script('core/app-assets/vendors/js/extensions/dragula.min.js') }}
    {{ Html::script('core/app-assets/js/scripts/pages/app-todo.js') }}
    {{ Html::script('focus/js/bootstrap-colorpicker.min.js') }}
    {{ Html::script('focus/js/select2.min.js') }}
    <script>
      
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#person").select2({
                tags: [],
                ajax: {
                    url: '{{route('biller.customers.select')}}',
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: function (person) {
                        return {
                            person: person
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name+' - '+item.company,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });

                 $("#region").select2({
                tags: [],
                ajax: {
                    url: '{{route('biller.regions.load_region')}}',
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    data: function (person) {
                        return {
                            person: person
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });


           $("#person").on('change', function () {
            $("#project").val('').trigger('change');
            var tips = $('#person :selected').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#project").select2({
                ajax: {
                    url: '{{route('biller.projects.project_load_select')}}?id=' + tips,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    params: {'cat_id': tips},
                    data: function (product) {
                        return {
                            product: product
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name+' - '+item.tid,
                                    id: item.id
                                }
                            })
                        };
                    },
                }
            });
        });


             $("#unit_type").on('change', function () {
            $("#indoor").val('').trigger('change');
            var tips = $('#unit_type :selected').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#indoor").select2({
                ajax: {
                    url: '{{route('biller.equipments.equipment_load')}}?id=' + tips,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 50,
                    params: {'cat_id': tips},
                    data: function (product) {
                        return {
                            product: product
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
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



           




  $(document).ready(function () {
            $('[data-toggle="datepicker"]').datepicker({
                autoHide: true,
                format: '{{config('core.user_date_format')}}'
            });
            $('[data-toggle="datepicker"]').datepicker('setDate', '{{date(config('core.user_date_format'))}}');
        });














    </script>
@endsection




