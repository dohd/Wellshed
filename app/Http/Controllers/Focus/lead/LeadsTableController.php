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

namespace App\Http\Controllers\Focus\lead;

use App\Http\Controllers\Controller;
use App\Models\Access\User\User;
use App\Models\account\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\lead\LeadRepository;
use Carbon\Carbon;

/**
 * Class BranchTableController.
 */
class LeadsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $lead;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(LeadRepository $lead)
    {

        $this->lead = $lead;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->lead->getForDataTable();

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['lead'], $ins);

        Storage::disk('local')->put('leads/Request.json', json_encode($request->all()));


        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->editColumn('client_status', function ($lead) {
                return $lead->client_status == 'customer'? 'existing' : $lead->client_status;
            })
            ->addColumn('tender_status', function ($lead) use($prefixes) {
                $stage = @$lead->tender->tender_stages;
                $type = @$lead->tender->type;
                if ($stage) return '<div class="badge" style="background-color: #16D39A">'. ucfirst($stage) .'</div><div><b>'. ucfirst($type) .'</b></div>';
            })
            ->editColumn('expected_income', function ($lead) {
                $currency = (string) @$lead->currency->code;
                $amount = numberFormat($lead->expected_income);
                return  "{$currency} {$amount}";
            })
            ->addColumn('reference', function ($lead) use($prefixes) {
                return gen4tid("{$prefixes[0]}-", $lead->reference);
            })
            ->addColumn('client_name', function ($lead) {
                $client_name = $lead->client_name;
                if ($lead->customer) $client_name = $lead->customer->company;
                if ($client_name && $lead->branch) $client_name .= " - {$lead->branch->name}";
                return $client_name;
            })
            ->addColumn('exact_date', function ($lead) {
                $days = '';
                if ($lead->exact_date) {
                    $exact = Carbon::parse($lead->exact_date);
                    $difference = $exact->diff(Carbon::now());
                    $days = $difference->days;
                    return $days;
                }
                return $days;
            })
            ->addColumn('source', function ($lead) {

                if (!empty($lead->LeadSource)) return $lead->LeadSource->name;
                else return 'UPDATE SOURCE SELECTION...';
            })
            ->addColumn('created_at', function ($lead) {
                return dateFormat($lead->created_at);
            })
            ->addColumn('status', function ($lead) {
                if ($lead->status) return '<div class="badge" style="background-color: #16D39A"> Closed </div>';
                return '<div class="badge" style="background-color: #00B5B8"> Open </div>';
            })
            ->addColumn('category', function ($lead) {

                $account = Account::where('account_type', 'Income')
                    ->where('id', $lead->category)
                    ->first();

                return empty($account) ? 'N/A' : $account->holder;
            })->addColumn('creator', function ($lead) {

                $creator = User::withoutGlobalScopes()->find($lead->user_id);

                return optional($creator)->first_name . ' ' . optional($creator)->last_name;
            })
            ->addColumn('actions', function ($lead) {
                return $lead->action_buttons;
            })
            ->rawColumns(['actions', 'status'])
            ->make(true);
    }
}
