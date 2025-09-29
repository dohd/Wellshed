<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="0" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Task</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('biller.send_sms.update_settings') }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editTitle">Title</label>
                        {{ Form::text('title', null, ['class' => 'form-control box-size', 'id' => 'editTitle']) }}
                    </div>
                    <div class="form-group">
                        <label for="editType">Type of Task</label>
                        <select name="type" id="editType" class="form-control" disabled>
                            <option value="">---select type of task---</option>
                            <option value="project_percentage">Quote Vs. Actual Expense (%)</option>
                            <option value="project_amount">Actual Expense vs. Budget (Amount)</option>
                            <option value="daily_metrics">8pm Daily Report</option>
                            <option value="budget_limit">QT/PI Budget Limit (%)</option>
                            <option value="technician_report">Technician Report</option>
                            <option value="birthdays">Birthday Notification</option>
                            <option value="kpi_report">KPI Summary Report</option>
                            <option value="document">Document Tracker Expiry</option>
                            <option value="milestone_expense">Milestone Expenses (%) vs Milestone Work Done (%)</option>
                            <option value="unapproved_quote">Notify Customer on Unapproved QT/PI</option>
                            <option value="lpo_notification">LPO Creation Notification</option>
                            <option value="tender_notification">Tender Notification</option>
                            <option value="project_completion">Project Completion Notification</option>
                            <option value="dlp_notification">DLP Notification</option>
                            <option value="ticket_notification">Ticket Notification</option>
                            <option value="mr_notification">Material Requisition Notification</option>
                            <option value="leave_notification">Leave Application Notification</option>
                            @if (auth()->user()->ins == 2)
                                
                            <option value="tenant_subscription">Package Subscription</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editUom">Unit of Measure</label>
                        <select name="uom" id="editUom" class="form-control">
                            <option value="">--select uom--</option>
                            @foreach (['%', 'AMOUNT'] as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editTarget">Target</label>
                        <input type="text" name="target" id="editTarget" class="form-control">
                        <input type="hidden" name="id" id="item_id">

                    </div>
                    <div class="form-group div_edit_users">
                        <label for="editRecipients">Recipients</label>
                        <select name="recipients[]" id="editRecipients" class="form-control"
                            data-placeholder="Search Recipients" multiple>
                            @foreach ($recipients as $user)
                                <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group div_edit_all_users d-none">
                        <label for="recipients">Receipients</label>
                        <select name="recipients[]" id="edit_all_users" class="form-control" data-placeholder="Search All Recipents" multiple disabled>
                            <option value="">Search All Recipients</option>
                            @foreach ($all_users as $user)
                                <option value="{{$user->id}}">{{$user->fullname}}-{{@$user->business->cname}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Send Email</label>
                        <select name="email" id="editEmail" class="form-control">
                            <option value="">--send email--</option>
                            @foreach (['yes', 'no'] as $item)
                                <option value="{{ $item }}">{{ ucfirst($item) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Send SmS</label>
                        <select name="sms" id="editSms" class="form-control">
                            <option value="">--send sms--</option>
                            @foreach (['yes', 'no'] as $item)
                                <option value="{{ $item }}">{{ ucfirst($item) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
