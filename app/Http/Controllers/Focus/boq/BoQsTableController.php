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
namespace App\Http\Controllers\Focus\boq;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\boq\BoQRepository;
use App\Http\Requests\Focus\boq\ManageboqRequest;

/**
 * Class boqsTableController.
 */
class BoQsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BoQRepository
     */
    protected $boq;

    /**
     * contructor to initialize repository object
     * @param BoQRepository $boq ;
     */
    public function __construct(BoQRepository $boq)
    {
        $this->boq = $boq;
    }

    /**
     * This method return the data of the model
     * @param ManageboqRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        //
        $core = $this->boq->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($boq) {
                  return '<a href="' . route('biller.boqs.show', $boq->id)  . '" class="" data-toggle="tooltip" data-placement="top" title="Generate">'.gen4tid("BoQ-",$boq->tid).'</a>';
            })
            ->addColumn('name', function ($boq) {
                  return $boq->name;
            })
            ->addColumn('customer', function ($boq) {
                $lead = $boq->lead;
                if (!$lead) {
                    return '';
                }

                if ($lead->customer) {
                    $customer = $lead->customer->company;
                    if ($lead->branch) {
                        $customer .= ' - ' . $lead->branch->name;
                    }
                } else {
                    $customer = $lead->client_name ?? '';
                }

                return $customer;
            })
            ->addColumn('total_boq_vat', function ($boq) {
                $vat = 0;
                $amount = floatval($boq->total_boq_amount);
                if ($amount > 0 && $boq->vat_type) {
                    if ($boq->vat_type == 'inclusive') {
                        $vat = 0.16 * $amount;
                    } elseif ($boq->vat_type == 'exclusive') {
                        $vat_base = $amount / 1.16;
                        $vat = $vat_base * 0.16;
                    }
                }
                return numberFormat($vat);
            })
            ->addColumn('boq_total', function ($boq) {
                $total = 0;
                $amount = floatval($boq->total_boq_amount);
                if ($amount > 0 && $boq->vat_type) {
                    if ($boq->vat_type == 'inclusive') {
                        $total = $amount;
                    } elseif ($boq->vat_type == 'exclusive') {
                        $total = $amount / 1.16;
                    }
                }
                return numberFormat($total);
            })
            ->addColumn('grand_total', function ($boq) {
                $grand_total = 0;
                $amount = floatval($boq->total_boq_amount);
                if ($amount > 0 && $boq->vat_type) {
                    if ($boq->vat_type == 'inclusive') {
                        $grand_total = 1.16 * $amount;
                    } elseif ($boq->vat_type == 'exclusive') {
                        $grand_total = $amount;
                    }
                }
                return numberFormat($grand_total);
            })
            ->addColumn('bom', function ($boq) {
                if ($boq->bom){
                    $btn = '<a href="' . route('biller.boms.show', $boq->bom)  . '" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="Generate">'.gen4tid("MTO-",$boq->bom->tid).'</a>';
                    if(access()->allow('manage-boms')){

                        return $btn;
                    }
                    return gen4tid("MTO-",$boq->bom->tid);
                } 
                return 'N/A';
            })
            ->addColumn('created_at', function ($boq) {
                return Carbon::parse($boq->created_at)->toDateString();
            })
            ->addColumn('actions', function ($boq) {
                $btn = '<a href="' . route('biller.boqs.generate_bom', $boq)  . '" 
                class="btn btn-secondary round" data-toggle="tooltip" data-placement="top"
                onclick="return confirm(\'Are you sure you want to generate BOM / MTO?\')"
                 title="Generate"><i  class="fa fa-gear"></i></a>';
                if(!$boq->bom && access()->allow('manage-boqs')) return $btn.$boq->action_buttons;
                return $boq->action_buttons;
            })
            ->make(true);
    }
}
