<?php

namespace App\Repositories\Focus\charge;

use DB;
use App\Models\charge\Charge;
use App\Exceptions\GeneralException;
use App\Repositories\Accounting;
use App\Repositories\BaseRepository;

/**
 * Class ChargeRepository.
 */
class ChargeRepository extends BaseRepository
{
    use Accounting;

    /**
     * Associated Repository Model.
     */
    const MODEL = Charge::class;

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
        DB::beginTransaction();

        $input = array_replace($input, [
            'date' => date_for_database($input['date']),
            'amount' => numberClean($input['amount'])
        ]);
        $result = Charge::create($input);

        /** accounting */
        $this->post_account_charge($result);
        
        if ($result) {
            DB::commit();
            return true;
        } 
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Charge $charge
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Charge $charge, array $input)
    {
        DB::beginTransaction();

        $input = array_map( 'strip_tags', $input);
        $result = $charge->update($input);

        /** accounting */
        $charge->transactions()->delete();
        $this->post_account_charge($charge);

        if ($result) {
            DB::commit();
            return true;
        } 
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Charge $charge
     * @throws GeneralException
     * @return bool
     */
    public function delete(Charge $charge)
    {
        DB::beginTransaction();
        $charge->transactions()->delete();
        if ($charge->delete()) {
            DB::commit();
            return true;
        }       
    }
}
