<?php

namespace App\Http\Middleware;

use App\Models\Access\User\User;
use App\Models\Company\Company;
use App\Models\customer\Customer;
use App\Models\tenant\Tenant;
use App\Models\tenant\TenantDeactivation;
use Closure;
use DateInterval;
use DateTime;

class SubscriptionCutter
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \DateMalformedIntervalStringException
     * @throws \DateMalformedStringException
     */
    public function handle($request, Closure $next)
    {
        $business = auth()->user()->business;
        if ($business->is_main) {
            $tenants = Tenant::orderBy('cname')->where('is_tenant', 1)->get();
            foreach ($tenants as $tenant) {
                // Retrieve tenant super user and update account balance
                $adminUser = User::where('ins', $tenant->id)->whereNotNull('tenant_customer_id')->first();
                $customer = Customer::withoutGlobalScopes()->find(@$adminUser->tenant_customer_id);
                if ($adminUser && $customer) {
                    $repository = new \App\Repositories\Focus\customer\CustomerRepository;
                    $controller = new \App\Http\Controllers\Focus\customer\CustomersController($repository);

                    $startDate = date('Y-m-d');
                    $endDate = "2000-01-01";
                    $customerBills = $controller->repository->agingFilteredBills($customer->id, $startDate);      
                    $agingCluster = $controller->customerAgingCluster($customerBills, $startDate, $endDate);
                    $accountBalance = array_sum($agingCluster);

                    $tenant->update(['subscription_balance' => $accountBalance]);
                }

                // return $details = json_encode(compact('cm','aging_cluster', 'adjustment_total', 'acc', 'account_balance'));
                // if ($account_balance > 0) return '<p style="color:red; font-size:18px;">' . numberFormat($account_balance) . '</p>';
                // return '<p style="color:green; font-size:18px;">' . numberFormat($account_balance) . '</p>';
            }
        } 

        if ($business->is_tenant) {
            $today = new DateTime();
            $billingDate = new DateTime($business->billing_date);
            $cutoffDate = new DateTime($business->cutoff_date);
            if (!$business->cutoff_date) {
                $cutoffDate = (clone $billingDate)->add(new DateInterval('P' . intval($business->grace_days) . 'D'));
            }

            // conditional checks for subscription expiry
            $check1 = $business->subscription_balance > 0 &&
                $today > $cutoffDate && $business->status === 'Active';
            $check2 = $today > $cutoffDate;
            // if (auth()->id() == 346) dd($check1, $check2, $today, $cutoffDate);

            if ($check1 || $check2) {
                $this->disableClientAccounts(0, $business->id);
                TenantDeactivation::create(['tenant_id' => $business->id]);
                // if (auth()->id() == 346) dd('before view');
                return response()->view('core.subscription_expired');
            }
            
            if ($business->status !== 'Active') {
                return response()->view('core.account-deactivated');
            }
        }
        
        return $next($request);
    }

    public function disableClientAccounts($allStatusZero, $clientBusinessId) 
    {
        $company = Company::find($clientBusinessId);
        $user = User::where('ins', $clientBusinessId);
        if ($allStatusZero) {
            $user->update(['login_access' => 1]);
            $company->update(['status' => 'Active']);
        } else {
            $user->update(['login_access' => 0]);
            $company->update(['status' => 'Suspended']);
        }

        $allStatusZero = $user->get()->every(fn ($user) => ($user->login_access === 0));
            

        // return new RedirectResponse(route('biller.tenants.index'), [
        //     'flash_success' => $allStatusZero ?
        //     "User Accounts for Client '" . $tenant->cname . "' Disabled Successfully!" :
        //     "User Accounts for Client '" . $tenant->cname . "' Enabled Successfully!"
        // ]);
    }
}
