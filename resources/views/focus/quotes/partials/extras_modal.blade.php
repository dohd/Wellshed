<div class="modal fade" id="extrasModal"  role="dialog" aria-labelledby="extrasModal" aria-hidden="true">
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
                        <label for="select_header">Select Header</label>
                        <select id="select_header" class="form-control" data-placeholder="Search Header">
                            <option value="">Search Header</option>
                            @foreach ($quote_notes as $note)
                                <option value="{{$note->id}}">{{$note->title}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-12">
                        <label for="header_extra">Header Details</label>
                        {{ Form::textarea('extra_header', null, ['class' => 'form-control html_editor','id'=>'header_text']) }}
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-12">
                        <label for="select_footer">Select Footer</label>
                        <select id="select_footer" class="form-control" data-placeholder="Search Footer">
                            <option value="">Search Footer</option>
                            @foreach ($quote_notes as $note)
                                <option value="{{$note->id}}">{{$note->title}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-12">
                        <label for="footer_extra">Footer Details</label>
                        {{ Form::textarea('extra_footer', null, ['class' => 'form-control html_editor','id'=>'footer_text']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>