<div class="modal" id="AttachDIModal" tabindex="-1" role="dialog" aria-labelledby="AttachDiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <section class="todo-form">
                <form action="{{route('biller.projects.store_quote_invoice')}}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="AttachDiLabel">Attach Detached Invoice To Quote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <fieldset class="form-group position-relative has-icon-right  col-md-12">
                                <div><label for="invoice">Project invoice</label></div>
                                <select id="dinvoice" name="invoice_id" class="form-control required" data-placeholder="Search Detached Invoice">
                                </select>
                            </fieldset>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <fieldset class="form-group position-relative has-icon-left mb-0">
                            <button type="submit"  class="btn btn-info">
                                <i class="fa fa-paper-plane-o d-block d-lg-none"></i>
                                <span class="d-none d-lg-block">{{trans('general.add')}}</span>
                            </button>
                        </fieldset>
                    </div>
                    <input type="hidden" value="" id="quote_id_val" name="quote_id">
                    <input type="hidden" value="{{@$project->id}}" id="project_id" name="project_id">
                </form>
            </section>
        </div>
    </div>
</div>