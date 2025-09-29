<?php

namespace App\Http\Controllers\Focus\rfq;

use App\Http\Controllers\Controller;
use App\Models\purchase_requisition\PurchaseRequisition;
use App\Repositories\Focus\rfq\RfQRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class RfQTableController extends Controller
{
    protected $rfq;
    public function __construct(RfQRepository $rfq)
    {
        $this->rfq = $rfq;
    }
    public function __invoke()
    {
        $core = $this->rfq->getForDataTable();

        $prefixes = prefixesArray(['rfq'], auth()->user()->ins);
        // aggregate
        // $order_total = $core->sum('grandttl');
        $grn_total = 0;
        // foreach ($core as $po) {
        //     $grn_total += $po->grns->sum('total');
        // }
        // $aggregate = [
        //     'order_total' => numberFormat($order_total),
        //     'grn_total' => numberFormat($grn_total),
        //     'due_total' => numberFormat($order_total - $grn_total),
        // ];

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($rfq) use ($prefixes) {
                return '<a class="font-weight-bold" href="' . route('biller.rfq.show', $rfq) . '">' . gen4tid("RFQ-", $rfq->tid) . '</a>';
            })
            // ->addColumn('supplier', function ($po) {
            //     if ($po->supplier)
            //         return ' <a class="font-weight-bold" href="' . route('biller.suppliers.show', $po->supplier) . '">' . $po->supplier->name . '</a>';
            // })
            // ->addColumn('count', function ($po) {
            //     // return $po->items->count();
            //     return 1;
            // })
            ->addColumn('rfq_date', function ($rfq) {
                return dateFormat($rfq->date);
            })
            ->addColumn('due_date', function ($rfq) {
                return dateFormat($rfq->due_date);
            })
            // ->addColumn('amount', function ($po) {
            //     return numberFormat($po->grandttl);
            // })
            ->addColumn('pr_no', function ($rfq) {
                $purchase_requisition_ids = array_filter(explode(',', $rfq->purchase_requisition_ids));
                $prs = '';
                if(count($purchase_requisition_ids) > 0){
                 foreach ($purchase_requisition_ids as $i => $val) {
                     $pr = PurchaseRequisition::find($val);
                     $prs .= '<a class="font-weight-bold" href="' . route('biller.purchase_requisitions.show', $pr->id) . '">' . gen4tid('PR-',$pr->tid).'-'. $pr->note . '</a>' .'</br>';
                 }
                } else{
                    $prs = 'No PR';
                }
                return $prs;
            })
            ->addColumn('project_no', function ($rfq) {
                $purchase_requisition_ids = array_filter(explode(',', $rfq->purchase_requisition_ids));
                $prs = '';
                if(count($purchase_requisition_ids) > 0){
                 foreach ($purchase_requisition_ids as $i => $val) {
                     $pr = PurchaseRequisition::find($val);
                     if($pr->project){
                         $prs .= '<a class="font-weight-bold" href="' . route('biller.projects.show', $pr->project->id) . '">' . gen4tid('PRJ-',$pr->project->tid) .'-'.$pr->project->name . '</a>' .'</br>';
                     }
                 }
                } else{
                    $prs = 'Non Project';
                }
                return $prs;
            })
            ->addColumn('actions', function ($rfq) {
                $btn = '';
                $rfq_analysis_count = $rfq->rfq_analysis->count() ?? 0;

                // echo $rfq_analysis_count;
                if($rfq->status == 'approved' && $rfq_analysis_count < 1) {

                    $btn .= '<a href="'.route('biller.rfq_analysis.create_analysis', [$rfq]).'" class="btn btn-secondary round" data-toggle="tooltip" data-placement="top" title="Create RFQ Analysis"><i class="fa fa-gear" aria-hidden="true"></i></a> ';
                }

                return $btn.$rfq->action_buttons;
            })
            // ->addColumn('aggregate', function () use ($aggregate) {
            //     return $aggregate;
            // })
            ->make(true);
    }
}
