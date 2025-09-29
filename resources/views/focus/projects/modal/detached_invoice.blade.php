<div class="modal" id="AddDetachedInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="AddInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <section class="todo-form">
                <form id="data_form_invoice" class="invoice-input">
                    <div class="modal-header">
                        <h5 class="modal-title" id="AddInvoiceLabel">Attach Detached Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <fieldset class="form-group position-relative has-icon-right  col-md-12">
                                <div><label for="invoice">Project invoice</label></div>
                                <select multiple id="invoice" name="invoice_ids[]" class="form-control required" data-placeholder="Choose Invoice">
                                </select>
                            </fieldset>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="button" id="submit-data_invoice" class="btn btn-info add-invoice-item"data-dismiss="modal">
                                <i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">{{trans('general.add')}}</span>
                            </button>
                        </fieldset>
                    </div>
                    <input type="hidden" value="{{route('biller.projects.store_meta')}}" id="action-url_9">
                    <input type="hidden" value="{{$project->id}}" name="project_id">
                    <input type="hidden" value="9" name="obj_type">
                </form>
            </section>
        </div>
    </div>
</div>