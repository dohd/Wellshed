<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\customer\CustomersController;
use App\Models\Access\User\User;
use App\Models\customer\Customer;
use App\Models\items\JournalItem;
use App\Repositories\Focus\customer\CustomerRepository;
use App\Repositories\Focus\tenant\TenantRepository;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;


class TenantsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $tenant;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(TenantRepository $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->tenant->getForDataTable();

        try {

            return Datatables::of($core)
                ->escapeColumns(['id'])
                ->addIndexColumn()
                ->addColumn('tid', function ($tenant) {
                    return gen4tid('SP-', $tenant->tid);
                })
                ->editColumn('status', function ($tenant) {
                    $variant = 'badge-secondary';
                    if ($tenant->billing_status == 'onboarding') {
                        $variant = 'badge-warning';

                        return '<span class="badge ' . $variant . '">' . ucfirst($tenant->billing_status) . '</span>';
                    }
                    else if ($tenant->status == 'Active') $variant = 'badge-success';
                    else if ($tenant->status == 'Terminated') $variant = 'badge-danger';
                    return '<span class="badge ' . $variant . '">' . $tenant->status . '</span>';
                })
                ->addColumn('service', function ($tenant) {


                    $package = optional(optional(optional($tenant)->package)->service)->package;

                    if (!empty(json_decode($package))) return $package->first()->name;

                    else return '<span style="color: #F9D448"><i> <b>Package Not Selected</b> </i></span>';
                })
                ->addColumn('pricing', function ($tenant) {

//                return json_encode(optional(optional($tenant)->package)->service);

                    $package = optional(optional(optional($tenant)->package)->service)->package;


                    if (!empty(json_decode($package))) {

                        $packagePrice = $package->first()->price;
                        $maintPrice = optional(optional($tenant)->package)->service->maintenance_cost;

                        return amountFormat($packagePrice + $maintPrice);
                    } else return '<span style="color: #F9D448"><i> <b>Package Not Selected</b> </i></span>';
                })
                ->addColumn('due_date', function ($tenant) {
                    $due_date = @$tenant->package->due_date;
                    if ($due_date) return date('d-M-Y', strtotime($due_date));
                    return '';
                })
                ->addColumn('billing_date', function ($tenant) {

                    if ($tenant->billing_date) return (new DateTime($tenant->billing_date))->format('jS F Y h:ia');
                    return '';
                })
                ->addColumn('grace_days', function ($tenant) {

                    return $tenant->grace_days ?? 0 . ' days';
                })
                ->addColumn('cutoff_date', function ($tenant) {

                    if ($tenant->billing_date) {

                        if ($tenant->grace_days > 0) $cutoffDate = (new DateTime($tenant->billing_date))->add(new DateInterval('P' . $tenant->grace_days . 'D'));
                        else $cutoffDate = new DateTime($tenant->billing_date);

                        return $cutoffDate->format('jS F Y h:ia');
                    }

                    return "N/A";
                })

                ->addColumn('loyalty_points', function ($tenant) {

                    return '<p style="color:gold; font-size:18px;">' . number_format($tenant->loyalty_points, 2) . '</p>';
                })
                ->addColumn('balance', function ($tenant) {

                    // Retrieve the admin user's customer_id
                    $adminUser = User::where('ins', $tenant->id)->whereNotNull('customer_id')->first();
                    if (!$adminUser || !$adminUser->customer_id) {
                        return null;
                    }

                    // Find the customer without global scopes
                    $customer = Customer::withoutGlobalScopes()->find($adminUser->customer_id);
                    if (!$customer) {
                        return null;
                    }

                    // Calculate adjustment total
                    $adjustment_total = JournalItem::where('customer_id', $customer->id)
                        ->whereHas('account', fn($q) =>
                        $q->whereHas('account_type_detail', fn($q) => $q->whereIn('system', ['receivable', 'loan']))
                        )
                        ->whereHas('journal', fn($q) => $q->whereNull('paid_invoice_id'))
                        ->sum(DB::raw('debit-credit'));

                    // Get customer invoices and calculate the aging cluster
                    $customerRepo = new CustomerRepository();
                    $customerController = new CustomersController($customerRepo);
                    $invoices = $customerController->statement_invoices($customer);
                    $aging_cluster = $customerController->aging_cluster($customer, $invoices);

                    // Calculate the account balance
                    $account_balance = collect($aging_cluster)->sum() + $adjustment_total - $customer->on_account;


                    if ($account_balance > 0) return '<p style="color:red; font-size:18px;">' . numberFormat($account_balance) . '</p>';
                    return '<p style="color:green; font-size:18px;">' . numberFormat($account_balance) . '</p>';
                })
                ->addColumn('actions', function ($tenant) {
                    return $tenant->action_buttons;
                })
                ->make(true);
        }
        catch (Exception $ex) {

            return [
                'message' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ];


        }

    }

}
