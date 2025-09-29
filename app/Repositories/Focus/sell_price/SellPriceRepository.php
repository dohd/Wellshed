<?php

namespace App\Repositories\Focus\sell_price;

use DB;
use Carbon\Carbon;
use App\Models\sell_price\SellPrice;
use App\Exceptions\GeneralException;
use App\Models\sell_price\SellPriceItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SellPriceRepository.
 */
class SellPriceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = SellPrice::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get();
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
        DB::beginTransaction();
        foreach($data as $key => $val){
            if(in_array($key, ['percent_fixed_value','recommended_value']))
                $data[$key] = numberClean($val);
        }
        $result = SellPrice::create($data);
        $data_items = $input['data_items'];
        // dd($data, $data_items);
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'sell_price_id' => $result->id, 
                'ins' => $result->ins,
                'landed_price' =>  floatval(str_replace(',', '', $v['landed_price'])),
                'minimum_selling_price' => floatval(str_replace(',', '', $v['minimum_selling_price'])),
                'recommended_selling_price' => floatval(str_replace(',', '', $v['recommended_selling_price'])),
                'moq' => floatval(str_replace(',', '', $v['moq'])),
                'reorder_level' => floatval(str_replace(',', '', $v['reorder_level'])),
            ]);
        }, $data_items);
        SellPriceItem::insert($data_items);
        if ($result) {
            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.SellPrices.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param SellPrice $sell_price
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($sell_price, array $input)
    {
        $data = $input['data'];
        DB::beginTransaction();
        foreach($data as $key => $val){
            if(in_array($key, ['percent_fixed_value','recommended_value']))
                $data[$key] = numberClean($val);
        }
        $sell_price->update($data);
        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $sell_price->items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['landed_price', 'minimum_selling_price', 'recommended_selling_price', 'reorder_level','moq']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $sell_price_item = SellPriceItem::firstOrNew(['id' => $item['id']]);
            $sell_price_item->fill(array_replace($item, ['sell_price_id' => $sell_price['id'], 'ins' => $sell_price['ins']]));
            if (!$sell_price_item->id) unset($sell_price_item->id);
            $sell_price_item->save();
        }

        if($sell_price)
        {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.SellPrices.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param SellPrice $sell_price
     * @throws GeneralException
     * @return bool
     */
    public function delete($sell_price)
    {
        if ($sell_price->items()->delete() && $sell_price->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.SellPrices.delete_error'));
    }
}
