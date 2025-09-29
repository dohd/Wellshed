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
namespace App\Http\Controllers\Focus\leave_category;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\leave_category\LeaveCategoryRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class LeaveCategoryTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LeaveCategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LeaveCategoryRepository $repository ;
     */
    public function __construct(LeaveCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('gender', function ($leave_category) {
                switch ($leave_category->gender) {
                    case 'a': return 'All';
                    case 'm': return 'Male';
                    case 'f': return 'Female';
                }
            })
            ->addColumn('actions', function ($leave_category) {
                return $leave_category->action_buttons;
            })
            ->make(true);
    }
}
