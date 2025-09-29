<h4 class="ml-2">Uploads</h4>
<div class="form-group row">
    <div class="col-10">
        <div class="table-responsive">
            <table id="docTbl" class="table" widht="100%">
                <thead>
                    <tr>
                        <th>Caption</th>
                        <th>Document</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- schedule row template -->
                    <tr>
                        <td><input type="text" name="caption[]" class="form-control" id="caption-0"></td>
                        <td><input type="file" name="document_name[]" class="form-control" id="document_name-0"></td>
                        <input type="hidden" name="existing_document_name[]" value="example.png">
                        <td>
                            <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                <i class="fa fa-trash fa-lg text-danger"></i>
                            </button>
                        </td>
                        <input type="hidden" name="doc_id[]" value="0">
                    </tr>

                    <!-- edit contract task schedules -->
                    @if(@$casuals && count($casuals->casual_docs))
                        @foreach($casuals->casual_docs as $index => $row)
                            <tr>
                                <td><input type="text" name="caption[]" value="{{ $row->caption }}" class="form-control" id="caption-{{ $index }}"></td>
                                <td>
                                    <input type="file" name="document_name[]" class="form-control" id="document_name-{{ $index }}">
                                    @if($row->document_name)
                                        <p><a href="{{ Storage::disk('public')->url('files/casual_docs/' . $row->document_name) }}" target="_blank">{{ $row->document_name }}</a></p>
                                        <input type="hidden" name="existing_document_name[]" value="{{ $row->document_name }}">                                                
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-light btn-sm mt-1 remove_doc">
                                        <i class="fa fa-trash fa-lg text-danger"></i>
                                    </button>
                                </td>
                                <input type="hidden" name="doc_id[]" value="{{ $row->id }}">
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <button class="btn btn-success btn-sm ml-2" type="button" id="addDoc">
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Row
        </button>
    </div>
</div>