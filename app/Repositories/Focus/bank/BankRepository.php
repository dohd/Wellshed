<?php

namespace App\Repositories\Focus\bank;

use DB;
use Carbon\Carbon;
use App\Models\bank\Bank;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankRepository.
 */
class BankRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Bank::class;

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
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        $input = array_map( 'strip_tags', $input);
        if (@$input['feed_begin_date']) {
            $input['feed_begin_date'] = date_for_database($input['feed_begin_date']);
        }
        if (@$input['feed_begin_balance']) {
            $input['feed_begin_balance'] = numberClean($input['feed_begin_balance']);
        }
        $bank = Bank::create($input);
        return $bank;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bank $bank
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Bank $bank, array $input)
    {
        $input = array_map( 'strip_tags', $input);
        if (@$input['feed_begin_date']) {
            $input['feed_begin_date'] = date_for_database($input['feed_begin_date']);
        }
        if (@$input['feed_begin_balance']) {
            $input['feed_begin_balance'] = numberClean($input['feed_begin_balance']);
        }
    	return $bank->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Bank $bank
     * @throws GeneralException
     * @return bool
     */
    public function delete(Bank $bank)
    {
        return $bank->delete();
    }
}
