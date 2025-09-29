<legend class="mt-2">Upload Interim / Completion Certificate</legend><hr>
<div class="form-group row">
    <div class="col-6">
        <div class="table-responsive">
            <table id="docTbl" class="table" widht="50%">
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
                    @if(@$contract && count($contract->pm_docs))
                        @foreach($contract->pm_docs as $index => $row)
                            <tr>
                                <td><input type="text" name="caption[]" value="{{ $row->caption }}" class="form-control" id="caption-{{ $index }}"></td>
                                <td>
                                    <input type="file" name="document_name[]" class="form-control" id="document_name-{{ $index }}">
                                    @if($row->document_name)
                                        <p><a href="{{ Storage::disk('public')->url('img/pm_documents/' . $row->document_name) }}" target="_blank">{{ $row->document_name }}</a></p>
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
            <i class="fa fa-plus-square" aria-hidden="true"></i> Add Interim/Cert
        </button>
    </div>
    <div class="col-6">
        <div class="form-group row">
            <div class="col-12">
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="is_final" value="1" class="form-check-input"  id="isFinalVal"> 
                    <label class="form-check-label" for="isFinalVal">Is Final Valuation</label>
                </div>   
            </div>
            <div class="col-12">
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" id="select_completion">
                    <label class="form-check-label" for="DLPTracker">Add Completion Date & Track DLP</label><br>                
                </div>                
            </div>
        </div>
        <div class="form-group completion row d-none">
            <div class="col-4">
                <label for="completion_date">Completion Date</label><br>
                <input type="text" name="completion_date" id="completion_date" class="form-control">
            </div>
            <div class="col-4">
                <label for="">DLP Period (Months)</label>
                <input type="number" step="0.01" name="dlp_period" id="dlp_period" class="form-control">
            </div>
            <div class="col-4">
                <label for="">DLP End Date Reminder (Days)</label>
                <input type="number" step="0.1" name="dlp_reminder" id="dlp_reminder" class="form-control">
            </div>
            <div class="col-12 pt-1">
                <label for="select">Add Users for reminder</label>
                <select name="employee_ids[]" id="employee_ids" class="form-control" data-placeholder="Search Users" multiple>
                    <option value=""></option>
                    @foreach ($users as $user)
                        <option value="{{$user->id}}">{{$user->fullname}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
