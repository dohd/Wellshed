<?php

namespace App\Repositories\Focus\banktransfer;

use App\Models\banktransfer\Banktransfer;
use App\Exceptions\GeneralException;
use App\Models\bank\Bank;
use App\Repositories\BaseRepository;
use App\Repositories\Accounting;
use DB;

/**
 * Class BanktransferRepository.
 */
class BanktransferRepository extends BaseRepository
{
    use Accounting;
    /**
     * Associated Repository Model.
     */
    const MODEL = Banktransfer::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('user_type'), function($q){
            if(request('user_type') == 'employee')
            {
                $q->where('employee_id', request('employee_id'));
            }
            elseif (request('user_type') == 'casual') {
                # code...
                $q->where('casual_id', request('casual_id'));
            }
            elseif (request('user_type') == 'third_party_user') {
                # code...
                $q->where('third_party_user_id', request('third_party_user_id'));
            }
        });
        return $q;
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

        foreach ($input as $key => $value) {
            if ($key == 'transaction_date') $input[$key] = date_for_database($value);
            $numKeys = ['amount', 'default_rate', 'bank_rate', 'receipt_amount', 'source_amount_fx', 'dest_amount_fx', 'fx_gain_total', 'fx_loss_total'];
            if (in_array($key, $numKeys)) $input[$key] = floatval(str_replace(',', '', $value));
        }

        $banktransfer = Banktransfer::create($input);

        /** accounting */
        $this->post_bank_transfer($banktransfer);

        if ($banktransfer) {
            DB::commit();
            return $banktransfer;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Bank $bank
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Banktransfer $banktransfer, array $input)
    {
        DB::beginTransaction();

        foreach ($input as $key => $value) {
            if ($key == 'transaction_date') $input[$key] = date_for_database($value);
            $numKeys = ['amount', 'default_rate', 'bank_rate', 'receipt_amount', 'source_amount_fx', 'dest_amount_fx', 'fx_gain_total', 'fx_loss_total'];
            if (in_array($key, $numKeys)) $input[$key] = floatval(str_replace(',', '', $value));
        }

        // dd($input);
        $result = $banktransfer->update($input);

        /** accounting */
        $banktransfer->transactions()->delete();
        $this->post_bank_transfer($banktransfer);

        if ($result) {
            DB::commit();
            return true;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Bank $bank
     * @throws GeneralException
     * @return bool
     */
    public function delete($banktransfer)
    {
        DB::beginTransaction();
        $banktransfer->transactions()->delete();
        $result = $banktransfer->delete();
        if ($result) {
            DB::commit();
            return true;
        }
    }
}
