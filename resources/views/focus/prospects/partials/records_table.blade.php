
<table id="records_table" class="table table-striped table-bordered zero-configuration recordsTable" cellspacing="0" width="100%">
    <thead>
        <tr class="bg-gradient-directional-blue white">
            <th class="text-center">#No</th>
            <th class="text-center">Question</th>
            <th class="text-center">Answer Type</th>
            <th class="text-center">Explanation</th>
        </tr>
    </thead>
    <tbody>
        @if ($items)
            @foreach ($items as $i => $item)
                <tr>
                    <td>{{$i+1}}</td>
                    <td>{{$item->prospect_question->question}}</td>
                    <td>{{$item->answer_type}}</td>
                    <td>{{$item->explanation}}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
