<div class="card-content">

    <div class="card-body">
        <div class="mb-1">
            <div class="h3">Total Purchases Value: </div>
            @if(empty($purchases->purchases))
                <div class="h2">0</div>
            @else
                <div class="h2"> <b> {{ number_format($purchases->purchases->sum('grandttl'), 2) }} </b> </div>
            @endif

        </div>
        <table id="purchasesTbl"
            class="table table-striped table-bordered zero-configuration" cellspacing="0"
            width="100%">
            <thead>
                <tr>
                    <th>DP Number</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Budget Line</th>
                    <th>Created By</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

</div>