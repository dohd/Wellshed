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

namespace App\Http\Controllers\Focus\calllist;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\calllist\CallListRepository;

/**
 * Class BranchTableController.
 */
class CallListTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $calllist;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $productcategory ;
     */
    public function __construct(CallListRepository $calllist)
    {

        $this->calllist = $calllist;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->calllist->getForDataTable();
       
        return Datatables::of($core)
        ->escapeColumns(['id'])
        ->addIndexColumn()
        ->addColumn('title', function ($calllist) {

            $title = $calllist->title == null ? '----' : $calllist->title;
            return $title;
        })
        ->addColumn('category', function ($calllist) {
            $category = $calllist->category == null ? '----' : $calllist->category ;

            return $category;
        })
        ->addColumn('prospects_number', function ($calllist) {
            $prospects_number = $calllist->prospects_number == null ? '----' : $calllist->prospects_number;

            return $prospects_number;
        })
        ->addColumn('explore', function ($calllist) {
            $link = route('biller.calllists.allocationdays', [$calllist]);
            return '<a id="explore" href="' . $link . '" class="btn btn-primary" data-toggle="tooltip"  title="Call" >
            <i  class="fa fa-vcard"></i>
                     </a>';
        })
        ->addColumn('start_date', function ($calllist) {
            $start_date = $calllist->start_date == null ? '----': $calllist->start_date;

            return $start_date;
        })
        ->addColumn('end_date', function ($calllist) {
            $end_date = $calllist->end_date == null ? '----' : $calllist->end_date;

            return $end_date;
        })
        ->addColumn('employee', function ($calllist) {
           $employee = $calllist->employee ? $calllist->employee->fullname :'';
           return $employee;
        })
        ->addColumn('number_of_not_called', function ($calllist) {
           $number_of_not_called = $calllist->prospects ? $calllist->prospects()->where('is_called', 0)->count() : 0;
           return $number_of_not_called;
        })
        ->addColumn('number_of_called', function ($calllist) {
           $number_of_called = $calllist->prospects ? $calllist->prospects()->where('is_called', 1)->count() : 0;
           return $number_of_called;
        })


        // ->addColumn('not_called_total', function ($calllist) {
        //    $count = $calllist->prospects ? $calllist->prospects()->where('is_called',0)->count() : 0;
        //    return $count;
        // })
        ->addColumn('actions', function ($calllist) {
            return $calllist->action_buttons;
        })
        ->make(true);
    }

}
