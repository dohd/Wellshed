<div class="modal" id="ProgressTaskModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-m" role="document">
        <div class="modal-content ">
                <div class="modal-header">
                    <h4 class="modal-title" id="progressModalLabel">Update Task Progress</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form action="{{route('biller.tasks.update_task_completion')}}" method="POST">
                        @csrf 
                        <div class="form-group">
                            <div class="col">
                                <label for="describe">Description</label>
                                <input type="text" id="" class="form-control" value="" name="description">
                            </div>
                            <div class="col mt-2">
                                <label for="date">Date</label>
                                <input type="text" class="form-control" name="date" id="date" />
                                <input type="hidden" value="{{$project->id}}" name="project_id" id="project"/>
                                <input type="hidden" value="0" name="task_id" id="task"/>
                            </div>
                            <div class="col mt-2">
                                <label for="task_percent">Current Percentage</label>
                                <input type="text" id="task_percent" readonly class="form-control">
                            </div>
                            <div class="col mt-2">
                                <label for="type">Type</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">-----Select Type -------</option>
                                    <option value="increment">Increment</option>
                                    <option value="decrement">Decrement</option>
                                </select>
                            </div>
                            <div class="col mt-2">
                                <label for="percent_qty">Increase/Decrease Percentage</label>
                                <input type="text" id="percent_qty" name="percent_qty"  class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</div>
