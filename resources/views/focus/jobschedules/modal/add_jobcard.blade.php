<div class="modal" id="edit_product_location_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content ">
            <section class="todo-form">
                <form id="data_form_jobcard" class="todo-input">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Write Job Card</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    	     <div class="form-group row">
                            <div class="col-md-12 col-xs-12 mt-1">
                                <div class="row">
                                    <label class="col-sm-12 col-xs-12 control-label"
                                           for="sdate">Date</label>

                                    <div class="col-sm-12 col-xs-12">
                                      <input type="text" class="form-control from_date required"
                                               placeholder="Start Date" name="job_date"
                                               autocomplete="false" data-toggle="datepicker">

                                       
                                    </div>
                                </div>
                            </div>
                         
                        </div>
                        <div class="row">
                            <fieldset class="form-group col-12">
                                <input type="text" class="new-todo-item form-control required"
                                       placeholder="Job Card Number" name="job_card">
                            </fieldset>
                        </div>
                         <div class="row">
                            <fieldset class="form-group col-12">
                                <input type="text" class="new-todo-item form-control required"
                                       placeholder="Technician" name="technician">
                            </fieldset>
                        </div>

                         <div class="row">
                             <fieldset class="form-group col-12">
                            <textarea class="new-todo-item form-control" placeholder="Remarks"
                                      rows="6" name="recommendation"></textarea>
                        </fieldset>
                        </div>



                    
                    </div>
                    <div class="modal-footer">
                        <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="button" id="submit-data_jobcard" class="btn btn-info add-todo-item"
                                    data-dismiss="modal"><i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">Save Record</span></button>
                        </fieldset>
                    </div>
                    
                    <input type="hidden" value="{{route('biller.projectequipments.write_job_card')}}" id="action-url_jobcard">
                    <input type="hidden" name="selected_eqipment"  id="selected_eqipment_id">
                </form>
            </section>
        </div>
    </div>
</div>