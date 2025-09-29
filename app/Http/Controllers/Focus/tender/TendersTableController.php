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
namespace App\Http\Controllers\Focus\tender;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\tender\TenderRepository;
use App\Http\Requests\Focus\tender\ManagetenderRequest;

/**
 * Class TendersTableController.
 */
class TendersTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var tenderRepository
     */
    protected $tender;

    /**
     * contructor to initialize repository object
     * @param tenderRepository $tender ;
     */
    public function __construct(TenderRepository $tender)
    {
        $this->tender = $tender;
    }

    /**
     * This method return the data of the model
     * @param ManagetenderRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['quote', 'proforma_invoice', 'lead', 'invoice'], $ins);
        $core = $this->tender->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('title', function ($tender) {
                 return $tender->title;
            })
            ->addColumn('description', function ($tender) {
                return $tender->description;
            })
            ->addColumn('customer', function ($tender) {
                $clientname = @$tender->lead->client_name ?: '';
                $branch = @$tender->lead->branch? $tender->lead->branch->name : '';
                $address = @$tender->lead->client_address ?: '';
                $email = @$tender->lead->client_email ?: '';
                $cell = @$tender->lead->client_contact ?: '';
                if ($tender->client) {
                    $clientname = $tender->client->company;						
                    $branch = $tender->branch? $tender->branch->name : '';
                    $address = $tender->client->address;
                    $email = $tender->client->email;
                    $cell = $tender->client->phone;
                }
                return $clientname . '  '. $branch;
            })
            ->addColumn('lead', function ($tender) use($prefixes) {
                $link = '';
                if ($tender->lead) {
                    $link = '<a href="'. route('biller.leads.show', $tender->lead) .'">'.gen4tid("{$prefixes[2]}-", $tender->lead->reference).'</a>';
                }
                return $link;
            })
            ->addColumn('tender_stages', function ($tender) {
                return ucfirst($tender->tender_stages);
            })
            ->addColumn('submission_date', function ($tender) {
                return dateFormat($tender->submission_date);
            })
            ->addColumn('days_to_submission', function ($tender) {
                $submissionDate = Carbon::parse($tender->submission_date);
                // Get today's date
                $today = Carbon::today();
                // Calculate remaining days
                $remainingDays = $today->diffInDays($submissionDate, false);
                // Return the result (ensure it doesn't return negative if past due)
                return $remainingDays > 0 ? $remainingDays : 0;
            })
            ->addColumn('site_visit_date', function ($tender) {
                return dateFormat($tender->site_visit_date);
            })
            ->addColumn('amount', function ($tender) {
                return numberFormat($tender->amount);
            })
            ->addColumn('bid_bond_amount', function ($tender) {
                return numberFormat($tender->bid_bond_amount);
            })
            ->addColumn('actions', function ($tender) {
                return $tender->action_buttons;
            })
            ->make(true);
    }
}
