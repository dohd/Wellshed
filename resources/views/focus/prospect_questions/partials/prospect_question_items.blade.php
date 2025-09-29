<div class="table-responsive">
    <table id="questionTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th class="text-center">#No</th>
                <th class="text-center">Question</th>
                <th class="text-center">Answer Type</th>
                <th class="text-center">Action</th>
            </tr>
    
        </thead>
        <tbody>
            <tr id="questionRow">
                <td><span class="numbering" id="increment-p0"></span></td>
                <td><input type="text" name="question[]" id="question-p0" class="form-control"></td>
                <td>
                    <select name="type[]" id="type-p0" class="form-control">
                        <option value="">Select Answer Type</option>
                        <option value="yes_no">Yes/No</option>
                        <option value="naration">Naration</option>
                    </select>
                </td>
                <input type="hidden" name="id[]" value="0">
                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
            </tr>

            @if (isset($prospect_questions))
                @foreach ($prospect_questions->questions as $k => $item)
                    <tr>
                        <td><span class="numbering" id="increment-p{{$k}}">{{$k+1}}</span></td>
                        <td><input type="text" value="{{$item->question}}" name="question[]" id="question-p{{$k}}" class="form-control"></td>
                        <td>
                            <select name="type[]" id="type-p{{$k}}" class="form-control">
                                <option value="">Select Answer Type</option>
                                <option value="yes_no" {{$item->type == 'yes_no' ? 'selected' : ''}}>Yes/No</option>
                                <option value="naration" {{$item->type == 'naration' ? 'selected' : ''}}>Naration</option>
                            </select>
                        </td>
                        <input type="hidden" name="id[]" value="{{$item->id}}">
                        <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    </tr>
                @endforeach
            @endif
    
        </tbody>
    </table>
</div>

<div class="mt-1">
        <button type="button" class="btn btn-success" id="addRow">
            <i class="fa fa-plus-square"></i> Add Row
        </button>
    
</div> 