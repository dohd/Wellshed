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
namespace App\Http\Controllers\Focus\standard_template;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\standard_template\StandardTemplateRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class StandardTemplatesTableController.
 */
class StandardTemplatesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var StandardTemplateRepository
     */
    protected $standard_template;

    /**
     * contructor to initialize repository object
     * @param StandardTemplateRepository $standard_template ;
     */
    public function __construct(StandardTemplateRepository $standard_template)
    {
        $this->standard_template = $standard_template;
    }

    /**
     * This method return the data of the model
     * @param Managestandard_templateRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->standard_template->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($standard_template) {
                 return $standard_template->name;
            })
            ->addColumn('description', function ($standard_template) {
                return $standard_template->description;
            })
            ->addColumn('created_at', function ($standard_template) {
                return Carbon::parse($standard_template->created_at)->toDateString();
            })
            ->addColumn('actions', function ($standard_template) {
                return $standard_template->action_buttons;
            })
            ->make(true);
    }
}
