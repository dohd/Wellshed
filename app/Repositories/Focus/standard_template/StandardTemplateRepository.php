<?php

namespace App\Repositories\Focus\standard_template;

use App\Exceptions\GeneralException;
use App\Models\standard_template\StandardTemplate;
use App\Models\standard_template\StandardTemplateItem;
use App\Repositories\BaseRepository;
use DB;

/**
 * Class StandardTemplateRepository.
 */
class StandardTemplateRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = StandardTemplate::class;

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
        $result = StandardTemplate::create($data);

        //Data items
        $data_items = $input['data_items'];
        // dd($data_items);
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'standard_template_id' => $result->id, 
                'ins' => $result->ins,
                'user_id' => $result->user_id,
                'qty' => floatval(str_replace(',', '', $v['qty'])),
            ]);
        }, $data_items);
        StandardTemplateItem::insert($data_items);
        if ($result) {
            DB::commit();
            return true;
        }
        throw new GeneralException('Error creating Finished Product Template');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param standard_template $standard_template
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($standard_template, array $input)
    {
        DB::beginTransaction();
        $data = $input['data'];
        $standard_template->update($data);

        $data_items = $input['data_items'];
        // remove omitted items
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $standard_template->standard_template_items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            foreach ($item as $key => $val) {
                if (in_array($key, ['qty']))
                    $item[$key] = floatval(str_replace(',', '', $val));
            }
            $standard_template_item = StandardTemplateItem::firstOrNew(['id' => $item['id']]);
            $standard_template_item->fill(array_replace($item, ['standard_template_id' => $standard_template['id'], 'ins' => $standard_template['ins']]));
            if (!$standard_template_item->id) unset($standard_template_item->id);
            $standard_template_item->save();
        }
        
    	if ($standard_template)
            DB::commit();
            return $standard_template;

        throw new GeneralException('Error updating Finished Product Template');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param standard_template $standard_template
     * @throws GeneralException
     * @return bool
     */
    public function delete($standard_template)
    {
        if ($standard_template->standard_template_items->each->delete() && $standard_template->delete()) {
            return true;
        }

        throw new GeneralException('Error deleting Finished Product Template');
    }
}
