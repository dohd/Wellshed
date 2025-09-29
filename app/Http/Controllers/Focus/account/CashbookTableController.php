<?php

namespace App\Http\Controllers\Focus\account;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;

/**
 * Class AccountsTableController.
 */
class CashbookTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageAccountRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageAccountRequest $request)
    {
        $controller = new AccountsController(new AccountRepository);
        $core = $controller->cashbookTransactions();
    
        $aggregate = [
            'sum_debit' => numberFormat($core->sum('debit')), 
            'sum_credit' => numberFormat($core->sum('credit')), 
            'balance' => numberFormat($core->sum('debit')-$core->sum('credit')),
        ];

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tr_date', function ($model) {
                return dateFormat($model->tr_date);
            })
            ->addColumn('note', function ($model) {
                return $model->note;
            })
            ->addColumn('tr_type', function ($model) {
                if ($model->debit > 0) return 'Receipt';
                return 'Payment';
            })
            ->addColumn('tid', function ($model) {
                $params = [];
                if ($model->deposit) {
                    $params = [route('biller.invoice_payments.show', $model->deposit->id), gen4tid('PMT-', $model->deposit->tid)];
                } elseif ($model->bill_payment) {
                    $params = [route('biller.billpayments.show', $model->bill_payment->id), gen4tid('RMT-', $model->bill_payment->tid)];
                } elseif ($model->transfer) {
                    $params = [route('biller.banktransfers.show', $model->transfer->id), gen4tid('XFER-', $model->transfer->tid)];
                } elseif ($model->manualjournal) {
                    $params = [route('biller.journals.show', $model->manualjournal->id), gen4tid('JNL-', $model->manualjournal->tid)];
                } elseif ($model->charge) {
                    $params = ['#', gen4tid('CHRG-', $model->charge->tid)];
                }
                if (count($params) !== 2) return;

                return '<a href="'. $params[0] .'">'. $params[1] .'</a>';
            })
            ->addColumn('account', function ($model) {
                return @$model->account->holder;
            })
            ->editColumn('debit', function ($model) {
                return numberFormat($model->debit);
            })
            ->editColumn('credit', function ($model) {
                return numberFormat($model->credit);
            })
            ->addColumn('aggregate', function ($model) use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }
}
