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
namespace App\Http\Controllers\Focus\attendance;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\attendance\AttendanceRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class AttendanceTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AttendanceRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AttendanceRepository $repository ;
     */
    public function __construct(AttendanceRepository $repository)
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
            ->addColumn('date', function ($attendance) {
                return dateFormat($attendance->date);
            })
            ->addColumn('employee', function ($attendance) {
                $employee = $attendance->employee()->withoutGlobalScopes(['status'])->first();
                if ($employee) 
                return $employee->first_name . ' ' . $employee->last_name;
            })
            ->addColumn('hrs', function ($attendance) {
                return +    $attendance->hrs;
            })
            ->addColumn('actions', function ($attendance) {
                return $attendance->action_buttons;
            })
            ->make(true);
    }
}
