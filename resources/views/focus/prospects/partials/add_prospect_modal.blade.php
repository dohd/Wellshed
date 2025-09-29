<div class="modal fade " id="addProspectModal" tabindex="-1" role="dialog" aria-labelledby="addProspectModal"
    aria-hidden="true">
    <div class="modal-dialog"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-remarks-label">Add Prospects</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>


            <div class="mx-2 mt-3">

                <div class="mb-2"><label for="group_title" class="caption">Group Title</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="title" name="title" class="form-control select"
                            data-placeholder="Choose Title" required>
                            
                            @foreach ($titles as $title)
                                <option value="{{ $title->title }}">
                                    {{ $title->title }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="mb-3"><label for="group_title" class="caption">Select Prospect</label>
                    <div class="input-group">
                        <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
                        <select id="prospects" class="form-control select" name="prospects[]" multiple="multiple">
                           
                        </select>
                    </div>
                </div>


                <button class="form-control btn btn-primary text-white mb-3" id="save_prospects">
                    Save Prospects
                </button>


            </div>






        </div>
    </div>
</div>
