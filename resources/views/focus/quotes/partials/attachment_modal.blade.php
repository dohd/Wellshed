<div class="modal fade" id="attachFileModal" tabindex="-1" role="dialog" aria-labelledby="attachFileModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Attach Files</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::model($quote, ['route' => array('biller.quotes.store_attachment', $quote), 'method' => 'POST', 'files' => true ]) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="caption">Caption</label>
                        {{ Form::text('caption', null, ['class' => 'form-control', 'required']) }}
                    </div>
                    <div class="form-group">
                        <label for="file">Select Fie</label><br>
                        {!! Form::file('document_name', array('class'=>'input' )) !!}  @if(@$quote->files)
                        <small>Empty File</small>
                    @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Attach', ['class' => "btn btn-primary"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>