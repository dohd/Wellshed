
{!! Form::hidden('prospect_id', null, [
    'class' => 'form-control ',
    
    'id' => 'picked_prospect_id',
]) !!}
 <div class="form-group row">
    <div class="col-sm-6"><label for="recepient" class="caption">Recepient Name</label>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-bookmark-o" aria-hidden="true"></span></div>
            {{ Form::text('recepient', null, ['class' => 'form-control ', 'placeholder' => 'Name', 'id'=>'picked_recepient' ]) }}
        </div>
    </div>
</div>


<div class="form-group row">
    <label for="prospect_question">Choose Type of Questions</label>
    <select name="prospect_question_id" id="prospect_question" class="form-control" data-placeholder="Choose Prospect Questions">
        <option value="">Choose Question</option>
        @foreach ($prospect_questions as $question)
            <option value="{{$question->id}}">{{$question->title}}</option>
        @endforeach
    </select>
</div>

<div class="form-group row">
    <table id="records_table" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
        <thead>
            <tr class="bg-gradient-directional-blue white">
                <th class="text-center">#No</th>
                <th class="text-center">Question</th>
                <th class="text-center">Answer Type</th>
                <th class="text-center">Explanation</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
</div>

<div id="demo" class="form-group row">
    <div class="col-md-6">
        <p>When do you think is the appropriate date</p>
        <div class="input-group">
            <div class="input-group-addon"><span class="icon-file-text-o" aria-hidden="true"></span></div>
            <input type="datetime-local" name="reminder_date" id="demo_date" class="form-control"/>
        </div>
        
    </div>
    <div class="col-4">
        <label for="temperate">Choose Type of Temperate</label>
        <select name="temperate" id="temperate" class="form-control" data-placeholder="Choose temperate">
            <option value="">Choose Temperate</option>
            @foreach (['hot','cold','warm'] as $item)
                <option value="{{$item}}">{{ucfirst($item)}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6">
        <p>Any Remarks?</p>
        {!! Form::textarea('any_remarks', null, ['class' => 'form-control ', 'rows'=>3, 'placeholder' => 'Notes/Remarks', 'id' => 'notes']) !!}
    </div>
</div> 