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

namespace App\Http\Controllers\Focus\djc;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\djc\DjcRepository;

/**
 * Class AccountsTableController.
 */
class DjcsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $djc;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $account ;
     */
    public function __construct(DjcRepository $djc)
    {
        $this->djc = $djc;
    }

    /**
     * This method returns the datatable view
     */
    public function __invoke()
    {
        $core = $this->djc->getForDataTable();

        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['djc_report', 'lead'], $ins);

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function($djc) use($prefixes) {
                return gen4tid("{$prefixes[0]}-", $djc->tid);
            })
            ->addColumn('customer', function ($djc) {
                $link = '';
                if ($djc->client) {
                    $customer = $djc->client->company;
                    if ($djc->branch) $customer .= " - {$djc->branch->name}";
                    $link = $customer . ' <a class="font-weight-bold" href="'. route('biller.customers.show', $djc->client) .'"><i class="ft-eye"></i></a>';
                }
                if ($djc->lead && !$link) {
                    $link = $djc->lead->client_name;
                }
                return $link;
            })
            ->addColumn('created_at', function ($djc) {
                return dateFormat($djc->created_at);
            })
            ->addColumn('lead_tid', function($djc) use($prefixes) {
                if ($djc->lead)
                return gen4tid("{$prefixes[1]}-", $djc->lead->reference);
            })
            ->addColumn('actions', function ($djc) {
                return '<a href="' . route('biller.print_djc', [$djc->id, 10, token_validator('', "d{$djc->id}", true), 1]) . '" target="_blank"  class="btn btn-purple round"><i class="fa fa-print"></i></a> '
                    . $djc->action_buttons;
            })
            ->make(true);
    }
}
