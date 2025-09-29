<div class="modal fade" id="reviewsModal" tabindex="-1" role="dialog" aria-labelledby="reviewsModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">LPO Review Comments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($po, ['route' => ['biller.purchaseorders.lpo_review_comment', $po], 'method' => 'post' ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="date">Date</label>
                        {{ Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'date']) }}
                    </div>
                    <div class="form-group">
                        <label for="Comment">Comment</label>
                        {{ Form::textarea('comment', null, ['class' => 'form-control', 'rows' => 4]) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Submit', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>