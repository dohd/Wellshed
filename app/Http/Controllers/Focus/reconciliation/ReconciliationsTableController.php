<?php

namespace App\Http\Controllers\Focus\reconciliation;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\reconciliation\ReconciliationRepository;

class ReconciliationsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ReconciliationRepository
     */
    protected $reconciliation;

    /**
     * contructor to initialize repository object
     * @param ReconciliationRepository $reconciliation ;
     */
    public function __construct(ReconciliationRepository $reconciliation)
    {
        $this->reconciliation = $reconciliation;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->reconciliation->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account', function ($recon) {
                return @$recon->account->holder;
            })
            ->editColumn('end_date', function ($recon) {
                return $recon->end_date;
            })
            ->editColumn('end_balance', function ($recon) {
                return numberFormat($recon->end_balance);
            })
            ->editColumn('balance_diff', function ($recon) {
                return numberFormat($recon->balance_diff);
            })
            ->editColumn('reconciled_on', function ($recon) {
                return $recon->reconciled_on? dateFormat($recon->reconciled_on) : '';
            })
            ->addColumn('status', function ($recon) {
                $bank_transfer_ids = $recon->items->pluck('bank_transfer_id')->filter()->implode(',');
                $charge_ids = $recon->items->pluck('charge_id')->filter()->implode(',');
                $deposit_ids = $recon->items->pluck('deposit_id')->filter()->implode(',');
                $journal_item_ids = $recon->items->pluck('journal_item_id')->filter()->implode(',');
                $payment_ids = $recon->items->pluck('payment_id')->filter()->implode(',');
                $creditnote_ids = $recon->items->pluck('creditnote_id')->filter()->implode(',');

                $params = compact('journal_item_ids', 'bank_transfer_ids', 'charge_ids', 'payment_ids', 'deposit_ids', 'creditnote_ids');
                $params = array_merge($params, ['account_id' => $recon->account_id, 'end_date' => $recon->end_date]);
                // overwrite request params
                request()->merge($params);

                $controller = new \App\Http\Controllers\Focus\reconciliation\ReconciliationsController;
                $accountItems =  $controller->accountItems()->original;
                $n = count($accountItems);

                $status = '';
                $this->statusText = '';
                if ($recon->balance_diff == 0 && $n == 0) {
                    $status = '<span class="badge bg-primary">Reconciled</span>';
                    $this->statusText = 'Reconciled';
                } elseif ($n > 0) {
                    $status = '<span class="badge bg-warning">Uncleared</span>';
                    $this->statusText = 'Uncleared';
                } else {
                    // Cleared
                }
                return $status;
            })
            ->addColumn('status_text', function ($recon) {
                return $this->statusText;
            })
            ->addColumn('actions', function ($recon) {
                return $recon->action_buttons;
            })
            ->make(true);
    }
}