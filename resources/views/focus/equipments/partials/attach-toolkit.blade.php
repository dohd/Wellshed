<div class="modal fade" id="attachEquipment" role="dialog" aria-labelledby="attachEquipment" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Attach ToolKit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::open(['route' => 'biller.equipments.attach', 'method' => 'post', 'id' => 'attach-equipment']) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="status">Equipment Number</label>
                        <div class='col'>
                            <input type="hidden" name="equipment_id" id="" value="{{$equipment->id}}">
                            {{ Form::text('equipment_number', gen4tid('Eq-', $equipment->id), ['class' => 'form-control box-size', 'id'=>'equipment_number','readonly']) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reason">ToolKit Name</label>
                        <div class='col'>
                            <select class="form-control" id="toolkit-name" data-placeholder="Search ToolKit"></select>
                            <input type="hidden" name="toolkit_id" id="toolkitId">
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Create', ['class' => "btn btn-primary"]) }}
                    {{-- <button type="button" class="btn btn-primary submit">Create</button> --}}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script>
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
     // On searching supplier
     $('#toolkit-name').change(function() {
        const name = $('#toolkit-name option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#toolkitId').val(id);
        $('#toolkit_name').val(name);
    });


    // load employees
    const toolkitUrl = "{{ route('biller.toolkits.select') }}";
    function toolkitData(data) {
        return {results: data.map(v => ({id: v.id, text: v.toolkit_name }))};
    }
    $('#toolkit-name').select2(select2Config(toolkitUrl, toolkitData));
    // select2 config
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            }
        }
    }
</script>
@endsection