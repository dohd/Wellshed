<div class="modal" id="AddLabourModal" role="dialog" aria-labelledby="data_project" aria-hidden="true">
    <div class="modal-dialog modal-m" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title content-header-title" id="data_project">Attach Employees</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"> 
                {{ Form::open(['route' => 'biller.labour_allocations.store_labour_items']) }}   
               <div class="form-group">
                <div class="col">
                    <label for="assigns">Assign Employees</label>
                    <input type="text" id="" class="form-control" value="{{$employee}}" readonly>
                </div>
                <div class="col mt-2">
                    <label for="date">Date</label>
                    <input type="date" class="form-control" name="date" id="date" >
                    <input type="hidden" value="{{$id}}" readonly name="labour_id" id="">
                </div>
                <div class="col mt-2">
                    <label for="hrs">Hours</label>
                    <input type="number" name="hrs" id="hrs" class="form-control">
                </div>
                <div class="col mt-2">
                    <label for="type">Type of Work Done</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">-----Select Type -------</option>
                        <option value="repair">Repair</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="installation">Installation</option>
                        <option value="others">Others</option>
                    </select>
                </div>
               </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        Create
                    </button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>