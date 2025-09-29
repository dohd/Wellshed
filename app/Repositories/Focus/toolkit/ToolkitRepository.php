<?php

namespace App\Repositories\Focus\toolkit;

use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\toolkit\Toolkit;
use App\Models\toolkit\ToolkitItems;
use App\Models\product\ProductVariation;


/**
 * Class ProductcategoryRepository.
 */
class ToolkitRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Toolkit::class;

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
        $toolkit = $input['toolkit'];
            foreach ($toolkit as $key => $val) {
                $rate_keys = [
                    'toolkit_name'
                ];
                
            }
            
        $result = Toolkit::create($toolkit);

        $toolkit_items = $input['toolkit_items'];
    
        $toolkit_items = array_map(function ($v) use($result) {
            
            return array_replace($v, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'toolkit_id' => $result->id,
            ]);
        }, $toolkit_items);
        
        foreach ($toolkit_items as $toolkit_items) {
            unset($toolkit_items['qty']);
            unset($toolkit_items['q']);
            ToolkitItems::insert($toolkit_items);
        }
       

        DB::commit();
        if ($result) return $result;   

        throw new GeneralException(trans('exceptions.backend.toolkit.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Toolkit $toolkit, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($toolkit as $key => $val) {
            $rate_keys = [
                'toolkit_name'
            ];
        }

        $result = $toolkit->update($data);

        $data_items = $input['data_items'];
        
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        //dd($item_ids);
        $toolkit->item()->whereNotIn('id', $item_ids)->delete();
        // create or update toolkit item
        foreach ($data_items as $item) {         
            $toolkit_item = ToolkitItems::firstOrNew(['id' => $item['id']]);   

            $item = array_replace($item, [
                'ins' => $toolkit->ins,
                'user_id' => $toolkit->user_id,
                'toolkit_id' => $toolkit->id,
            ]); 
        //     $getQty = ToolkitItems::where('id', $item['id'])->get()->first(); 
        //    if ($getQty) {
            
        //     $x = $getQty->quantity;
        //     $qty_updated = $item['quantity'];
            
        //     if ($qty_updated > $x) {
        //         $y = $qty_updated - $x;
        //         $variations = ProductVariation::where('id', $item['item_id'])->get()->first();
        //         $db_variation = $variations->qty;
        //         if ($y > $db_variation) {
        //             $variations->qty = $variations->qty - $db_variation;
        //             $getQty->quantity = $getQty->quantity + $db_variation;
        //             $variations->update();
        //             $getQty->update();
        //         }
        //         else{
        //             $variations->qty = $variations->qty - $y;
        //             $getQty->quantity = $getQty->quantity + $y;
        //             $variations->update();
        //             $getQty->update();
        //         }
        //         //dd($db_variation);
        //     }
            
        //    }
            $toolkit_item->fill($item);
            if (!$toolkit_item->id) unset($toolkit_item->id);
            $toolkit_item->save();
        }
        if ($result) {
            DB::commit();
            return $toolkit;
        }

        throw new GeneralException(trans('exceptions.backend.toolkitorders.update_error'));

    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($toolkit)
    {
        $toolkit_items = ToolkitItems::where('toolkit_id', $toolkit->id)->get();
        
        foreach ($toolkit_items as $key => $value) {
            $variations = ProductVariation::where('id',$value->item_id)->get()->first();
            $variations->qty = $variations->qty + $value->quantity;
            $variations->update();
            //dd($value->qty_issued);
        }
        
        if ($toolkit->delete() && $toolkit_items->each->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.toolkit.delete_error'));
    }
   
}
