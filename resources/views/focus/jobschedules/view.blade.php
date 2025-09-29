@extends ('focus.jobschedules.layout.view')
@section('customer_view')
    <ul class="nav nav-tabs nav-top-border no-hover-bg nav-justified"
        role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="active-tab1" data-toggle="tab"
               href="#active1" aria-controls="active1" role="tab"
               aria-selected="true">Serviced</a>
        </li>
       <!-- <li class="nav-item">
            <a class="nav-link " id="active-tab2" data-toggle="tab"
               href="#active2" aria-controls="active2"
               role="tab">Unserviced</a>
        </li>-->
        <li class="nav-item">
            <a class="nav-link " id="active-tab3" data-toggle="tab"
               href="#active3" aria-controls="active3"
               role="tab">Write Job Card</a>
        </li>
        

    </ul>
    <div class="tab-content px-1 pt-1">
        <div class="tab-pane active in" id="active1"
             aria-labelledby="active-tab1" role="tabpanel">
         

           <div class="table-responsive">
                <table id="serviced-table"
                       class="table table-striped table-bordered zero-configuration font-small-2" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>

                                            
                                            <th>Region</th>
                                            <th>Branch</th>
                                            <th>Section</th>
                                            <th>Location</th>
                                            <th>Serial</th>
                                            <th>Manufacturer</th>
                                            <th>Model</th>
                                            <th>JobCard </th>
                                            <th>Technician </th>
                                            <th>Serviced Date </th>
                                            <th>Note </th>
                                         
                                            
                                            <th>{{ trans('general.createdat') }}</th>
                    </tr>
                    </thead>


                    <tbody></tbody>
                </table>
            </div>

         
         
            
        </div>
       <!-- <div class="tab-pane" id="active2" aria-labelledby="link-tab2"
             role="tabpanel">
             <div class="table-responsive">
                <table id="customers-table"
                       class="table table-striped table-bordered zero-configuration font-small-2" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>

                        <th>{{ trans('customers.name') }}</th>

                        <th>{{ trans('customers.email') }}</th>
                        <th>{{ trans('customers.address') }}</th>

                        <th>{{ trans('general.searchable') }}</th>
                        <th>{{ trans('labels.general.actions') }}</th>
                    </tr>
                    </thead>


                    <tbody></tbody>
                </table>
            </div>
        </div>-->
        <div class="tab-pane" id="active3" aria-labelledby="link-tab3"
             role="tabpanel">

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

             <div class="table-responsive">
                <table id="jobcard-table"
                       class="table table-striped table-bordered zero-configuration font-small-2" cellspacing="0"
                       width="100%">
                    <thead>
                         <tr>
                                            <th><input type="checkbox" id="select-all-row"></th>
                                           
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


                    <tbody></tbody>
                            <tfoot>
            <tr>
                <td colspan="12">
                <div style="display: flex; width: 100%;">
                 
                   
                
                        <button type="button" class="btn btn-xs btn-success update_product_location" data-type="add">Write Job Card</button>
                    &nbsp;
                     {{ Form::open(['route' => 'biller.projectequipments.store', 'class' => 'form-horizontal', 'role' => 'form', 'method' => 'post', 'id' => 'mass_deactivate_form']) }}
                       
                     {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']); !!}
                        {!! Form::submit('Deactivate Selected', array('class' => 'btn btn-xs btn-danger', 'id' => 'delete-selected')) !!}

                  {{ Form::close() }}
                   
                  
                    </div>
                </td>
            </tr>
        </tfoot>
                </table>
            </div>
            
        </div>
   
    </div>

@endsection