<?php

namespace App\Repositories\Focus\loan;

use DB;
use App\Exceptions\GeneralException;
use App\Models\account\Account;
use App\Models\loan\Loan;
use App\Models\transaction\Transaction;
use App\Models\transactioncategory\Transactioncategory;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\loan\LoanItem;
/**
 * Class CustomerRepository.
 */
class LoanRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Loan::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
       
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
         //dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'date') $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'fee', 'month_installment'])) 
                $input[$key] = numberClean($val);
        }

        $result = Loan::create($input);
        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.customers.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Loan $loan, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'approval_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'fee', 'month_installment', 'loan_type'])) 
                $input[$key] = numberClean($val);
        }

        $result = $loan->update($input);
        if (!$loan->employee) throw ValidationException::withMessages(['server error! something went wrong']);

        if ($loan->approval_status == 'approved') {
            $loan->amount += $loan->fee; 
          

            $loan->transactions()->delete();
            $this->post_transaction($loan);
            //dd($loan->id);
            if($loan->loan_type){
                $dates = [];

                // Get the current date
                $currentDate = Carbon::now();

                $dates[] = $currentDate->format('Y-m-d');

                // Generate 5 dates per month
                for ($i = 1; $i < $loan->month_period; $i++) {
                    // Add $i months to the current date
                    $date = $currentDate->addMonthNoOverflow();

                    // Add the date to the array
                    $dates[] = $date->toDateString();
                }
                foreach ($dates as $date) {
                    $loan_item = new LoanItem();
                    $loan_item->loan_id = $loan->id;
                    $loan_item->payment_date = $date;
                    $loan_item->month_installment = $loan->month_installment;
                    $loan_item->status = 0;
                    $loan_item->month_period = $loan->month_period;
                    $loan_item->ins = auth()->user()->ins;
                    $loan_item->user_id = auth()->user()->id;
                    $loan_item->save();
                }
               // dd($dates);
            }
        }

        if ($result) {
            DB::commit();
            return $result;
        }        
            
        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     *  Remove resource from storage
     */
    public function delete($loan)
    {
        DB::beginTransaction();

        $loan->transactions()->delete();
        aggregate_account_transactions();
        
        if ($loan->delete()) {
            DB::commit();
            return true;
        };
    }

    /**
     * Approve loan transaction
    */
    public function post_transaction($loan)
    {
        // credit lender account (bank)
        $tr_category = Transactioncategory::where('code', 'loan')->first(['id', 'code']);
        $tid = Transaction::where('ins', auth()->user()->ins)->max('tid') + 1;
        $cr_data = [
            'tid' => $tid,
            'account_id' => $loan->lender_id,
            'trans_category_id' => $tr_category->id,
            'credit' => $loan->amount,
            'tr_date' => $loan->approval_date,
            'due_date' => $loan->approval_date,
            'user_id' => $loan->user_id,
            'ins' => $loan->ins,
            'tr_type' => $tr_category->code,
            'tr_ref' => $loan->id,
            'user_type' => 'employee',
            'is_primary' => 1,
            'note' => $loan->note,
        ];
        Transaction::create($cr_data);

        unset($cr_data['credit'], $cr_data['is_primary']);
        if ($loan->employee) {
            // debit Loan Receivable
            $account = Account::where('system', 'loan_receivable')->first();
            $dr_data = array_replace($cr_data, [
                'account_id' =>  $account->id,
                'debit' => $loan->amount,
            ]);
            Transaction::create($dr_data);
        } else {
            // business loan
        }
        aggregate_account_transactions();    
    }
}