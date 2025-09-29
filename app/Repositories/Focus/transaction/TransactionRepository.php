<?php

namespace App\Repositories\Focus\transaction;

use DB;
use App\Models\transaction\Transaction;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class TransactionRepository.
 */
class TransactionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Transaction::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        // filter by user or account
        $q->when(request('rel_id'), function($q) {
            if (request('rel_type') == 9) {
                // query account
                $q->where('account_id', request('rel_id'));
                // dd('query account',request('rel_id'));
            } else if (request('rel_type') == 2) {
                // query user 
                $q->where('user_id', request('rel_id'));
            }
        });

        // filter by date
        $q->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('tr_date', [date_for_database(request('start_date')),date_for_database(request('end_date'))]);
        });
        // fetch related double-entry transactions
        $q->when(request('tr_id'), fn($q) => $q->where('tid', request('tr_tid', 0))->where('id', '!=', request('tr_id', 0)));

        // filter by customer, supplier, classlist, project
        $q->when(request('customer_id'), function($q) {
            $q->where(function($q) {
                $q->where('customer_id', request('customer_id'));
                $q->orWhereHas('project', fn($q) => $q->where('customer_id', request('customer_id')));
            });
        });
        $q->when(request('supplier_id'), fn($q) => $q->where('supplier_id', request('supplier_id')));
        $q->when(request('classlist_id'), fn($q) => $q->where('classlist_id', request('classlist_id')));
        $q->when(request('project_id'), fn($q) => $q->where('project_id', request('project_id')));
        // exempt journal entries
        $q->when(request('exempt_cols') && in_array('man_journal_id', request('exempt_cols')), function($q) {
            $q->whereNull('man_journal_id');
        });

        // order case of account filter
        $q->when(request('rel_id'), fn($q) =>$q->orderBy('tr_date', 'DESC'));

        return $q;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param App\Models\Transaction $transaction
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($transaction, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $input['due_date'] = $input['tr_date'];
        foreach ($input as $key => $val) {
            if (in_array($key, ['debit', 'credit']))
                $input[$key] = numberClean($val);
            if (in_array($key, ['tr_date', 'due_date'])) {
                $input[$key] = date_for_database($val);
            }
        }

        $result = $transaction->update($input);
        aggregate_account_transactions();

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Transaction $transaction
     * @return bool
     * @throws GeneralException
     */
    public function delete($transaction)
    {
        if ($transaction->reconciliation_id) return false;
        return $transaction->delete();

        throw new GeneralException(trans('exceptions.backend.transactions.delete_error'));
    }
}
