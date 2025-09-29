<?php

namespace App\Repositories\Focus\product_refill;

use App\Exceptions\GeneralException;
use App\Models\product_refill\ProductRefill;
use App\Models\product_refill\ProductRefillItem;
use App\Repositories\BaseRepository;
use DB;

class ProductRefillRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ProductRefill::class;

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
     * @return ProductRefill $product_refill
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'next_date', 'rem_start_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['rem_frequency'])) $input[$key] = numberClean($val);
        }
        
        $product_ids = $input['product_id'];
        unset($input['product_id']);
        $result = ProductRefill::create($input);

        $data_items = array_map(function($v) use($result) {
            return ['product_refill_id' => $result->id, 'product_id' => $v];
        }, $product_ids);
        ProductRefillItem::insert($data_items);

        if ($result) {
            DB::commit();
            return $result;
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param ProductRefill $product_refill
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(ProductRefill $product_refill, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'next_date', 'rem_start_date'])) $input[$key] = date_for_database($val);
            if (in_array($key, ['rem_frequency'])) $input[$key] = numberClean($val);
        }
        
        $product_ids = $input['product_id'];
        unset($input['product_id']);
        $result = $product_refill->update($input);

        $product_refill->items()->delete();
        $data_items = array_map(function($v) use($product_refill) {
            return ['product_refill_id' => $product_refill->id, 'product_id' => $v];
        }, $product_ids);
        ProductRefillItem::insert($data_items);

        if ($result) {
            DB::commit();
            return $product_refill;
        }
    }

    /**
     * For deleting the respective model from storage
     *
     * @param ProductRefill $product_refill
     * @throws GeneralException
     * @return bool
     */
    public function delete(ProductRefill $product_refill)
    {
        if ($product_refill->items()->delete() && $product_refill->delete()) 
        return true;
    }
}
