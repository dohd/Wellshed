<div class="sidebar-detached sidebar-left d-none d-sm-block">
    <div class="sidebar">
        <div class="bug-list-sidebar-content">
            <div class="card">
                <div class="card-head">
                    <div class="media-body media p-1">
                        <div class="media-middle pr-1">
                            <span class="avatar avatar-lg rounded-circle ml-2">
                                <img src="{{ Storage::disk('public')->url('app/public/img/customer/' . $customer->picture) }}" alt="avatar">
                            </span>
                        </div>
                    </div>
                    <div class="ml-1">
                        <h5 class="info">Customer</h5>
                        @php
                            $prefix = prefixesArray(['customer'], auth()->user()->ins);
                        @endphp
                        <h5 class="media-heading">{{ $customer->company }} | <b>{{($prefix ? $prefix[0] . '-' : 'CMR-') . $customer->id }}</b></h5>
                        <h5>Balance: <span class="text-danger">{{ numberFormat($account_balance) }}</span></h5>
                    </div>
                </div>
                <div class="card-body">
                    <table id="customerTbl" class="table table-striped table-bordered zero-configuration" cellspacing="0" width="100%">
                        <thead>
                            <tr><th>{{ trans('customers.name') }}</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="100%" class="text-center text-success font-large-1">
                                    <i class="fa fa-spinner spinner"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>