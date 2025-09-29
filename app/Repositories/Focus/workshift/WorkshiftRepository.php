<?php

namespace App\Repositories\Focus\workshift;

use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\workshift\Workshift;
use App\Models\workshift\WorkshiftItems;
use App\Models\product\ProductVariation;


/**
 * Class ProductcategoryRepository.
 */
class WorkshiftRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Workshift::class;

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
        $workshift = $input['workshift'];
            foreach ($workshift as $key => $val) {
                $rate_keys = [
                    'name'
                ];
                
            }
            
        $result = Workshift::create($workshift);

        $workshift_items = $input['workshift_items'];
        
    
        $workshift_items = array_map(function ($v) use($result) {
            
            return array_replace($v, [
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'workshift_id' => $result->id,
                'is_checked' => $v['status']
            ]);
        }, $workshift_items);
        
        foreach ($workshift_items as $workshift_items) {
            unset($workshift_items['qty'], $workshift_items['status']);
            unset($workshift_items['q']);
            WorkshiftItems::insert($workshift_items);
        }
       

        DB::commit();
        if ($result) return $result;   

        throw new GeneralException(trans('exceptions.backend.workshift.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(workshift $workshift, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($workshift as $key => $val) {
            $rate_keys = [
                'name'
            ];
        }

        $result = $workshift->update($data);

        $data_items = $input['data_items'];
        
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        //dd($item_ids);
        $workshift->item()->whereNotIn('id', $item_ids)->delete();
        // create or update workshift item
        foreach ($data_items as $item) {         
            $workshift_item = WorkshiftItems::firstOrNew(['id' => $item['id']]);   

            $item = array_replace($item, [
                'ins' => $workshift->ins,
                'user_id' => $workshift->user_id,
                'workshift_id' => $workshift->id,
                'is_checked' => $item['status'],
            ]); 
            unset($item['status']);
            $workshift_item->fill($item);
            if (!$workshift_item->id) unset($workshift_item->id);
            $workshift_item->save();
        }
        if ($result) {
            DB::commit();
            return $workshift;
        }

        throw new GeneralException(trans('exceptions.backend.workshiftorders.update_error'));

    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($workshift)
    {
        $workshift_items = WorkshiftItems::where('workshift_id', $workshift->id)->get();
        
        foreach ($workshift_items as $key => $value) {
            $variations = ProductVariation::where('id',$value->item_id)->get()->first();
            $variations->qty = $variations->qty + $value->quantity;
            $variations->update();
            //dd($value->qty_issued);
        }
        
        if ($workshift->delete() && $workshift_items->each->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.workshift.delete_error'));
    }
   
}
