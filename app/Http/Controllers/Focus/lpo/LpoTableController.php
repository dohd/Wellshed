<?php

namespace App\Http\Controllers\Focus\lpo;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class LpoTableController extends Controller
{
    /**
     * Tracks lpo balance from quotes
     * @var float 
     */
    protected $balance;
    protected $quote_cluster;

    /**
     * Quote Cluster
     */
    public function quote_cluster($lpo)
    {
        $cluster = [];
        foreach (['invoiced', 'verified_uninvoiced', 'approved_unverified'] as $val) {
            $cluster[$val] = array('links' => [], 'total' => 0);
        }
        foreach ($lpo->quotes as $quote) {
            $tid = gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid);
            $link = '<a href="'. route('biller.quotes.show', $quote) .'"><b>'. $tid .'</b></a>';
            if ($quote->invoiced == 'Yes') {
                $cluster['invoiced']['links'][] = $link;
                $cluster['invoiced']['total'] += $quote->verified_total;
            }
            if ($quote->verified == 'Yes' && $quote->invoiced == 'No') {
                $cluster['verified_uninvoiced']['links'][] = $link;
                $cluster['verified_uninvoiced']['total'] += $quote->verified_total;
            }
            if ($quote->status == 'approved' && $quote->verified == 'No') {
                $cluster['approved_unverified']['links'][] = $link;
                $cluster['approved_unverified']['total'] += $quote->total;
            }
        }
        return $cluster;
    }
    
    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = LpoController::getForDataTable();   
     
        return DataTables::of($core)
            ->addIndexColumn()
            ->escapeColumns(['id'])
            ->addColumn('quote_cluster', function ($lpo) {
                $this->quote_cluster = $this->quote_cluster($lpo);
            }) 
            ->addColumn('customer', function ($lpo) {
                $customer = $lpo->customer?  $lpo->customer->company : '';
                $branch = $lpo->branch ? $lpo->branch->name : '';
                if ($customer && $branch) return "{$customer} - {$branch}";
            })
            ->addColumn('lpo_no', function ($lpo) {
                $links = [];
                foreach ($lpo->quotes as $quote) {
                    if ($quote->invoice) {
                        $invoice = $quote->invoice;
                        $links[] = '<a href="'. route('biller.invoices.show', $invoice->id) .'">'. gen4tid('Inv-', $invoice->tid) .'</a>';
                    }
                }

                return $lpo->lpo_no . '<br>' . implode(', ', array_unique($links));
            })
            ->addColumn('amount', function ($lpo) {
                $this->balance = $lpo->amount;
                return '<span><b>' . numberFormat($lpo->amount) . '</b></span>';
            })
            ->addColumn('invoiced', function ($lpo) {
                $links = $this->quote_cluster['invoiced']['links']; 
                $total = $this->quote_cluster['invoiced']['total'];
                $this->balance -= $total;
                if ($total) 
                return '<span><b>' . numberFormat($total) . '</b><span><br>' . implode(', ', $links);
            })
            ->addColumn('verified_uninvoiced', function ($lpo) {
                $links = $this->quote_cluster['verified_uninvoiced']['links']; 
                $total = $this->quote_cluster['verified_uninvoiced']['total'];
                $this->balance -= $total;
                if ($total)
                return '<span><b>' . numberFormat($total) . '</b><span><br>' . implode(', ', $links);
            })
            ->addColumn('approved_unverified', function ($lpo) {
                $links = $this->quote_cluster['approved_unverified']['links']; 
                $total = $this->quote_cluster['approved_unverified']['total'];
                $this->balance -= $total;
                if ($total) 
                return '<span><b>'.numberFormat($total).'</b><span><br>' . implode(', ', $links);
            })
            ->addColumn('balance', function ($lpo) {
                return '<span><b>'.numberFormat($this->balance).'</b></span>';
            })
            ->addColumn('actions', function ($lpo) {
                return '<a href="'.$lpo->id.'" class="update-lpo" data-toggle="modal" data-target="#updateLpoModal"><i class="ft-edit fa-lg"></i></a>  '
                    .'<a href="'.route('biller.lpo.delete_lpo', $lpo->id).'" class="danger delete-lpo">'
                    .'<i class="fa fa-trash fa-lg"></i></a>';
            })
            ->make(true);
    }
}
