<fieldset class="border p-1 mb-3">
    <legend class="w-auto float-none h5">Order Items</legend>
    <div class="table-responsive" style="max-height: 80vh">
        <table id="quotation" class="table tfr my_stripe_single pb-2 text-center">
            <thead>
                <tr class="item_header bg-gradient-directional-blue white">
                    <th width="5%">#</th>
                    <th width="35%">Item Name</th>
                    <th width="7%">UoM</th>
                    <th width="7%">Qty</th>
                    <th width="10%">{{trans('general.rate')}}</th>
                    <th width="15%">VAT</th>
                    <th width="10%">{{trans('general.amount')}} </th>
                    <th width="12%">Remark</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <br>
    <div class="row">
        <div class="col-10 col-xs-7">
            <a href="javascript:" class="btn btn-success btn-sm mr-1" aria-label="Left Align" id="add-product">
                <i class="fa fa-plus-square"></i> Product Row
            </a>
            <a href="javascript:" class="btn btn-primary btn-sm" aria-label="Left Align" id="add-title">
                <i class="fa fa-plus-square"></i> Title Row
            </a>
        </div>
    </div>
</fieldset>
