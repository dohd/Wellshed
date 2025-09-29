<?php

namespace App\Repositories\Focus\bom;

use DB;
use Carbon\Carbon;

use App\Exceptions\GeneralException;
use App\Models\bom\BoM;
use App\Models\bom\BoMItem;
use App\Repositories\BaseRepository;

/**
 * Class bomRepository.
 */
class BoMRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = BoM::class;

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
        $result = BoM::create($data);

        $data_items = $input['data_items'];
        // dd($data_items);
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'bom_id' => $result->id, 
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'rate' =>  floatval(str_replace(',', '', $v['rate'])),
                'qty' => floatval(str_replace(',', '', $v['qty'])),
                'new_qty' => floatval(str_replace(',', '', $v['new_qty'])),
                'amount' => floatval(str_replace(',', '', $v['amount'])),
            ]);
        }, $data_items);
        BoMItem::insert($data_items);
        if ($result) {
            DB::commit();
            return $result;
        }
        throw new GeneralException(trans('exceptions.backend.boms.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param bom $bom
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($bom, array $input)
    {
        DB::beginTransaction();

        $data = $input['data'];   
        foreach ($data as $key => $val) {
            if (in_array($key, ['taxable', 'total', 'subtotal', 'tax'])) 
                $data[$key] = numberClean($val);
        }  
        
        $result = $bom->update($data);

        $data_items = $input['data_items'];
        $boq_sheet_id = $input['boq_sheet_id'];

        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $bom->items()->where('boq_sheet_id', $boq_sheet_id)->whereNotIn('id', $item_ids)->delete();
        
        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['rate', 'qty', 'amount','product_subtotal','tax_rate']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $bom_item = BoMItem::firstOrNew(['id' => $item['id']]);
            $bom_item->fill(array_replace($item, ['bom_id' => $bom['id'], 'ins' => $bom['ins']]));
            if (!$bom_item->id) unset($bom_item->id);
            $bom_item->save();
        }

    	if ($result){
            DB::commit();
            return;
        }
            

        throw new GeneralException(trans('exceptions.backend.boms.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param bom $bom
     * @throws GeneralException
     * @return bool
     */
    public function delete($bom)
    {
        if ($bom->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.boms.delete_error'));
    }
}
