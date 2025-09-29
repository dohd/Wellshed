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
namespace App\Http\Controllers\Focus\job_category;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\job_category\JobCategoryRepository;

/**
 * Class job_categoriesTableController.
 */
class JobCategoriesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var JobCategoryRepository
     */
    protected $job_category;

    /**
     * contructor to initialize repository object
     * @param JobCategoryRepository $job_category ;
     */
    public function __construct(JobCategoryRepository $job_category)
    {
        $this->job_category = $job_category;
    }

    /**
     * This method return the data of the model
     * @param Managejob_categoryRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->job_category->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($job_category) {
                 return $job_category->name;
            })
            ->editColumn('description', function ($job_category) {
                 return Str::limit($job_category->description, 60, '...');
            })
            ->editColumn('rate', function ($job_category) {
                 return number_format($job_category->rate, 2);
            })
            ->addColumn('actions', function ($job_category) {
                return $job_category->action_buttons;
            })
            ->make(true);
    }
}
