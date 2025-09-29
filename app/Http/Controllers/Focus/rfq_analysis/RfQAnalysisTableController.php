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
namespace App\Http\Controllers\Focus\rfq_analysis;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Repositories\Focus\rfq_analysis\RfQAnalysisRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class RfQAnalysisTableController.
 */
class RfQAnalysisTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var RfQAnalysisRepository
     */
    protected $rfq_analysis;

    /**
     * contructor to initialize repository object
     * @param RfQAnalysisRepository $rfq_analysis ;
     */
    public function __construct(RfQAnalysisRepository $rfq_analysis)
    {
        $this->rfq_analysis = $rfq_analysis;
    }

    /**
     * This method return the data of the model
     * @param Managerfq_analysisRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->rfq_analysis->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('subject', function ($rfq_analysis) {
                 return $rfq_analysis->subject;
            })
            ->addColumn('tid', function ($rfq_analysis) {
                return gen4tid('RFQA-',$rfq_analysis->tid);
            })
            ->addColumn('rfq_tid', function ($rfq_analysis) {
                return '<a href="' . route('biller.rfq.show', $rfq_analysis->id) . '">' . '<i>' . gen4tid('RFQ-',@$rfq_analysis->rfq->tid) . '</i> ' . '</a> <b> | </b>' . @$rfq_analysis->rfq->subject;
            })
            ->addColumn('date', function ($rfq_analysis) {
                return dateFormat($rfq_analysis->date);
            })
            ->addColumn('supplier', function ($rfq_analysis) {
                return $rfq_analysis->supplier ? $rfq_analysis->supplier->company : '';
           })
            ->addColumn('status', function ($rfq_analysis) {
                $status = '';
                if ($rfq_analysis->status == 'approved') {
                    $status = '<span style="color: green;"><b>Approved</b></span>';
                } else {
                    $status = '<span style="color: red;"><b>'.ucfirst($rfq_analysis->status).'</b></span>';
                }
                return $status;
            })
            ->addColumn('actions', function ($rfq_analysis) {
                return $rfq_analysis->action_buttons;
            })
            ->make(true);
    }
}
