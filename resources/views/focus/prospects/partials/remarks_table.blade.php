    <table id="remarks_table" class="table p-1 table-striped table-bordered remarkTable">
        <thead>
            <tr>
                <td>NO.</td>
                <td>CREATED AT</td>
                <td>RECEIPIENT</td>
                <td>REMARK</td>
                <td>REMINDER DATE</td>
            </tr>
        </thead>

        <tbody>

            @if ($remarks->count())
                @foreach ($remarks as $index => $remark)
                    <tr id="{{ $index + 1 }}">
                        <td> {{ $index + 1 }} </td>
                        <td> {{ $remark->created_at }} </td>
                        <td> {{ $remark->recepient }} </td>
                        <td> {{ $remark->any_remarks }} </td>
                        <td> {{ $remark->reminder_date }} </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align: center; vertical-align: middle;" colspan="5"> No record found </td>
                </tr>
            @endif

        </tbody>
    </table>
