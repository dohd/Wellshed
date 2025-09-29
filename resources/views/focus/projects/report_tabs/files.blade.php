<div class="tab-pane" id="tab_data8" aria-labelledby="tab8" role="tabpanel">

    <table id="files" class="files table table-striped mt-2">
        <thead>
            <tr>
                <th>#</th>
                <th>View File</th>
                <th>Captions</th>
            </tr>
        </thead>
        @foreach($project->attachment as $i => $row)
            <tr>
                <td>
                    {{$i+1}}
                </td>
                <td>
                    <a href="{{ asset('storage/app/public/files/' . $row['value']) }}" target="_blank" class="purple">
                        <i class="btn-sm fa fa-eye"></i> {{ $row['value'] }}
                    </a>
                </td>
                <td>
                    {{ $row['caption'] }}
                </td>
            </tr>
        @endforeach
    </table>
</div>