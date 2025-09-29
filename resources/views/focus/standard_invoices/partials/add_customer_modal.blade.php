<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-status-label">Add Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{ Form::open(['route' => 'biller.invoices.create_customer', 'method' => 'POST']) }}
                <div class="modal-body">                    
                    <div class="row mb-1">
                        <div class="col-6">
                            <label for="company" class="caption">Company*</label>
                            {{ Form::text('company', null, ['class' => 'form-control', 'id' => 'companyName']) }}
                        </div>
                        <div class="col-6">
                            <label for="name" class="caption">Client Name</label>
                            {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'clientName']) }}
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-6">
                            <label for="email" class="caption">Email</label>
                            {{ Form::text('email', null, ['class' => 'form-control']) }}
                        </div>
                        <div class="col-6">
                            <label for="phone" class="caption">Phone</label>
                            {{ Form::text('phone', null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-6">
                            <label for="address" class="caption">Address</label>
                            {{ Form::text('address', null, ['class' => 'form-control']) }}
                        </div>
                        <div class="col-6">
                            <label for="tax_pin" class="caption">Tax Pin</label>
                            {{ Form::text('tax_pin', null, ['class' => 'form-control']) }}
                        </div>
                    </div>                      
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary round" data-dismiss="modal">Close</button>
                    {{ Form::submit('Submit', ['class' => "btn btn-primary round"]) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>