<div class="modal fade" id="dettachEquipment" role="dialog" aria-labelledby="dettachEquipment" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Dettach ToolKit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ Form::open(['route' => 'biller.equipments.dettach', 'method' => 'post', 'id' => 'attach-equipment']) }}
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
                            <select class="form-control tool" name="toolkit_name" id="toolkit_name" data-placeholder="Search ToolKit">
                                {{ browserlog($equipment->toolkits) }}
                                @if($equipment->toolkits)
                                @foreach ($equipment->toolkits as $item)
                                <option value="{{$item->id}}">{{$item->toolkit_name}}</option>
                                @endforeach
                                @endif
                            </select>
                            <input type="hidden" name="toolkit_id" id="toolkitId">
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Dettach', ['class' => "btn btn-danger"]) }}
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
    $('form').submit(function (e) { 
        e.preventDefault();
        console.log($(this).serializeArray());
    });
     // On searching supplier
     $('.tool').change(function() {
        alert('Hello')
        const name = $('#toolkit_name').find(":selected").val();
    });
</script>
@endsection