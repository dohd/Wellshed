<?php

namespace App\Repositories\Focus\petty_cash;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\petty_cash\PettyCash;
use App\Models\petty_cash\PettyCashItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PettyCashRepository.
 */
class PettyCashRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = PettyCash::class;

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
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'expected_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax_amount','amount_given','balance']))
                $data[$key] = numberClean($val);
        }
        DB::beginTransaction();
        $data['balance'] = $data['amount_given'] - $data['total'];
        $result = PettyCash::create($data);
        
        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            $tax = floatval(str_replace(',', '', $v['itemtax'] ?? 0));
            unset($v['itemtax']);
            return array_replace($v, [
                'petty_cash_id' => $result->id, 
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'price' =>  floatval(str_replace(',', '', $v['price'])),
                'amount' => floatval(str_replace(',', '', $v['amount'])),
                'tax_rate' => floatval(str_replace(',', '', $v['tax_rate'])),
                'tax' => $tax,
            ]);
        }, $data_items);
        // dd($data_items);
        PettyCashItem::insert($data_items);
        if ($result) {
            DB::commit();
            return true;
        }
        throw new GeneralException('Error Creating Petty Cash');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param PettyCash $PettyCash
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($petty_cash, array $input)
    {
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['date', 'expected_date']))
                $data[$key] = date_for_database($val);
            if (in_array($key, ['total', 'subtotal', 'tax_amount','amount_given','balance']))
                $data[$key] = numberClean($val);
        }
        DB::beginTransaction();
        $data['balance'] = $data['amount_given'] - $data['total'];
        $petty_cash->update($data);
        
        $data_items = $input['data_items'];
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $petty_cash->items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['price', 'amount', 'tax_rate', 'itemtax']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $item['tax'] = $item['itemtax'];
            unset($item['itemtax']);
            // dd($item);
            $petty_cash_item = PettyCashItem::firstOrNew(['id' => $item['id']]);
            $petty_cash_item->fill(array_replace($item, ['petty_cash_id' => $petty_cash['id'], 'ins' => $petty_cash['ins']]));
            if (!$petty_cash_item->id) unset($petty_cash_item->id);
            $petty_cash_item->save();
        }
    
        if ($petty_cash) {
            DB::commit();
            return true;
        }
        throw new GeneralException('Error Updating Petty Cash');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param PettyCash $PettyCash
     * @throws GeneralException
     * @return bool
     */
    public function delete($petty_cash)
    {
        if ($petty_cash->items()->delete() && $petty_cash->delete()) {
            return true;
        }

        throw new GeneralException('Error Deleting Petty Cash!');
    }
}
