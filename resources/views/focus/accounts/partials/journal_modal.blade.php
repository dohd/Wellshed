<div class="modal fade" id="journalModal" tabindex="-1" role="dialog" aria-labelledby="journalModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Journal Entries</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
                <div class="modal-body">
                    <div class="responsive" style="max-height:80vh;overflow-y:auto">
                        <table id="journal-tbl" class="table table-sm" width="100%">
                            <thead class="">
                                <tr class="bg-gradient-x-info white">
                                    <th>Account</th>
                                    <th width="20%">Payee</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100%" class="text-center text-success font-large-1">
                                        <i class="fa fa-spinner spinner"></i>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th></th>
                                    <th>0.00</th>
                                    <th>0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
        </div>
    </div>
</div>