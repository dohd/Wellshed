<?php

namespace App\Repositories\Focus\part;

use App\Models\part\Part;
use App\Exceptions\GeneralException;
use App\Models\part\PartItem;
use App\Repositories\BaseRepository;
use DB;

/**
 * Class PartRepository.
 */
class PartRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Part::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->get();
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
        // dd($input);
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['total_qty'])) 
                $data[$key] = numberClean($val);
        }   

        $result = Part::create($data);

        //Data items
        $data_items = $input['data_items'];
        // dd($data_items);
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'part_id' => $result->id, 
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'qty' => floatval(str_replace(',', '', $v['qty'])),
            ]);
        }, $data_items);
        PartItem::insert($data_items);
        if ($result) {
            DB::commit();
            return true;
        }
        throw new GeneralException('Error creating Product Part');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Part $Part
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($part, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['total_qty'])) 
                $data[$key] = numberClean($val);
        } 
        $part->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $part->part_items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['qty','qty_for_single']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $part_item = PartItem::firstOrNew(['id' => $item['id']]);
            $part_item->fill(array_replace($item, ['part_id' => $part['id'], 'ins' => $part['ins']]));
            if (!$part_item->id) unset($part_item->id);
            $part_item->save();
        }
        
    	if ($part)
            DB::commit();
            return $part;

        throw new GeneralException('Error updating Product Part');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Part $Part
     * @throws GeneralException
     * @return bool
     */
    public function delete($part)
    {
        if ($part->part_items->each->delete() && $part->delete()) {
            return true;
        }

        throw new GeneralException('Error deleting Product Part');
    }
}
