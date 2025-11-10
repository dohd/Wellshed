<div class="modal fade" id="upgradeModal" tabindex="-1" role="dialog" aria-labelledby="upgradeModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-75">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Upgrade Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="upgradeForm">
                @csrf
                {{ Form::hidden('subscription_id', $subscription->id) }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="plan">Subscription Plans</label>
                        <select class="form-control" name="sub_package_id" id="package">
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}">
                                    {{ $package->name }} (KES {{ numberFormat($package->price) }} / month)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {{ Form::submit('Update', ['class' => "btn btn-primary"]) }}
                </div>
            </form>
        </div>
    </div>
</div>