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
namespace App\Http\Controllers\Focus\billpayment;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\billpayment\BillPaymentRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class BillPaymentTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BillPaymentRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BillPaymentRepository $repository ;
     */
    public function __construct(BillPaymentRepository $repository)
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
        $query = $this->repository->getForDataTable();
        $query_1 = clone $query;
        $prefixes = ['RMT', 'BILL'];
        // aggregate
        $amount_total = $query_1->sum('amount');
        $unallocated_total = $amount_total - $query_1->sum('allocate_ttl');
        $aggregate = [
            'amount_total' => numberFormat($amount_total),
            'unallocated_total' => numberFormat($unallocated_total),
        ];

        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->editColumn('tid', function ($billpayment) use ($prefixes) {
                return gen4tid("{$prefixes[0]}-", $billpayment->tid);
            })
            ->filterColumn('tid', function($query, $tid) use ($prefixes){
                $arr = explode('-',$tid);
                if (strtolower($arr[0]) == strtolower($prefixes[0]) && isset($arr[1])) {
                    $query->where('tid', floatval($arr[1]));
                }
                elseif (floatval($tid)) {
                    $query->where('tid', floatval($tid));
                }
            })
            ->addColumn('supplier', function ($billpayment) {
                $supplier_name = @$billpayment->supplier->name;
                if ($supplier_name) {
                    $supplier_names = [];
                    foreach ($billpayment->bills as $bill) {
                        if (@$bill->purchase->suppliername && preg_match("/walk/i", @$bill->purchase->supplier_type)) {
                            $supplier_names[] = @$bill->purchase->suppliername;
                        }
                    }
                    if ($supplier_names) return implode(', ', $supplier_names);
                }
                return $supplier_name;
            }) 
            ->filterColumn('supplier', function($query, $supplier) {
                $query->whereHas('supplier', fn($q) => $q->where('name', 'LIKE', "%{$supplier}%"));
            })   
            ->addColumn('account', function ($billpayment) {
                if ($billpayment->account)
                return $billpayment->account->holder;
            })
            ->filterColumn('account', function($query, $account) {
                $query->whereHas('account', fn($q) => $q->where('holder', 'LIKE', "%{$account}%"));
            })
            ->addColumn('date', function ($billpayment) {
                return dateFormat($billpayment->date);
            })
            ->orderColumn('date', 'date $1')
            ->editColumn('amount', function ($billpayment) {
                return numberFormat($billpayment->amount);
            })
            ->orderColumn('amount', function ($query, $order) {
                $query->orderBy('amount', $order);
            })
            ->addColumn('unallocated', function ($billpayment) {
                return numberFormat($billpayment->amount - $billpayment->allocate_ttl);
            })
            ->editColumn('payment_mode', function ($billpayment) {
                $pmt_type = ucfirst(str_replace('_', ' ', $billpayment->payment_type));
                return "{$billpayment->payment_mode} - {$billpayment->reference} <br> ({$pmt_type})";
            })
            ->filterColumn('payment_mode', function($query, $text) {
                $query->where('payment_type', 'LIKE', "%{$text}%");
            })
            ->addColumn('bill_no', function ($billpayment) use ($prefixes) {
                $links = [];
                foreach ($billpayment->bills as $bill) {
                    $tid = gen4tid("{$prefixes[1]}-", $bill->tid);
                    $links[] = '<a href="'. route('biller.utility_bills.show', $bill) .'">'.$tid.'</a>';
                }
                
                return implode(', ', $links);
            })
            ->addColumn('purchase_no', function ($billpayment) use ($prefixes) {
                $links = [];
                foreach ($billpayment->bills as $bill) {
                    if($bill->purchase){

                        $tid = gen4tid("DP-", $bill->purchase->tid);
                        $links[] = '<a href="'. route('biller.purchases.show', $bill->purchase->tid) .'">'.$tid.'</a>';
                    }
                }
                
                return implode(', ', $links);
            })
            ->filterColumn('bill_no', function($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[1]) && isset($arr[1])) {
                    $query->whereHas('bills', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('bills', fn($q) => $q->where('tid', floatval($tid)));
                }
            })
            ->orderColumn('bill_no', function ($query, $tid) use($prefixes) {
                $arr = explode('-', $tid);
                if (strtolower($arr[0]) == strtolower($prefixes[1]) && isset($arr[1])) {
                    $query->whereHas('bills', fn($q) => $q->where('tid', floatval($arr[1])));
                } elseif (floatval($tid)) {
                    $query->whereHas('bills', fn($q) => $q->where('tid', floatval($tid)));
                }
            })
            ->addColumn('aggregate', function ($billpayment) use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($billpayment) {
                return $billpayment->action_buttons;
            })
            ->make(true);
    }
}
