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
namespace App\Http\Controllers\Focus\goodsreceivenote;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class GoodsReceiveNoteTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var GoodsreceivenoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param GoodsreceivenoteRepository $repository ;
     */
    public function __construct(GoodsreceivenoteRepository $repository)
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

        $good_worth = $core->where('fx_curr_rate', 1)->sum('total');
        foreach ($core->where('fx_curr_rate', '>', 1) as $grn) {
            $good_worth += round($grn->total * $grn->fx_curr_rate, 4);
        }
        $aggregate = ['good_worth' => numberFormat($good_worth)];

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('tid', function ($grn) {
                return gen4tid('GRN-', $grn->tid);
            })
            // ->filterColumn('tid', function($query, $tid) {
            //     $arr = explode('-', $tid);
            //     if (strtolower($arr[0]) == strtolower('GRN') && isset($arr[1])) {
            //         $query->where('tid', floatval($arr[1]));
            //     } elseif (floatval($tid)) {
            //         $query->where('tid', floatval($tid));
            //     }
            // })
            ->addColumn('supplier', function ($grn) {
                if ($grn->supplier)
                return $grn->supplier->name.'  <b>('.$grn->supplier->taxid.')</b>';
            })      
            // ->filterColumn('supplier', function ($query, $keyword) {
            //     $query->whereHas('supplier', function ($query) use ($keyword) {
            //         $query->where('name', 'like', "%{$keyword}%")
            //               ->orWhere('taxid', 'like', "%{$keyword}%");
            //     });
            // })  
            ->addColumn('purchase_type', function ($grn) {
                $po = $grn->purchaseorder;
                if ($po) {
                    $lpo_no = '<a href="'. route('biller.purchaseorders.show', $po) .'">'. gen4tid('PO-', $po->tid) .'</a>';
                    $note = $po->note;
                    return "({$lpo_no}) - {$note}";
                }
            })
            ->addColumn('dnote', function ($grn) {
                return $grn->dnote;
            })
            ->addColumn('date', function ($grn) {
                return dateFormat($grn->date);
            })
            // ->orderColumn('date', '-date $1')
            ->addColumn('total', function ($grn) {
                return numberFormat($grn->total);
            })
            ->addColumn('actions', function ($grn) {
                return $grn->action_buttons;
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }
}
