<div class="modal fade" id="extrasModal" tabindex="-1" role="dialog" aria-labelledby="extrasModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Extra Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row form-group">
                    <div class="col-12">
                        <label for="header_extra">Header Details</label>
                        {{ Form::textarea('extra_header', null, ['class' => 'form-control html_editor']) }}
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-12">
                        <label for="footer_extra">Footer Details</label>
                        {{ Form::textarea('extra_footer', null, ['class' => 'form-control html_editor']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>