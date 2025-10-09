<?php

namespace App\Repositories\Focus\target_zone;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\target_zone\TargetZone;
use App\Models\target_zone\TargetZoneItem;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TargetZoneRepository.
 */
class TargetZoneRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TargetZone::class;

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
        DB::beginTransaction();
        $data = $input['data'];
        $result = TargetZone::create($data);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($result) {
            return array_replace($v, [
                'target_zone_id' => $result->id, 
                'ins' => $result->ins,
            ]);
        }, $data_items);
        TargetZoneItem::insert($data_items);
        if ($result) {
            DB::commit();
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.target_zones.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($target_zone, array $input)
    {
    	DB::beginTransaction();
        $data = $input['data'];
        $result = $target_zone->update($data);

        $data_items = $input['data_items'];
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $target_zone->items()->whereNotIn('id', $item_ids)->delete();

        // create or update items
        foreach($data_items as $item) {
            $target_zone_item = TargetZoneItem::firstOrNew(['id' => $item['id']]);
            $target_zone_item->fill(array_replace($item, ['target_zone_id' => $target_zone['id'], 'ins' => $target_zone['ins']]));
            if (!$target_zone_item->id) unset($target_zone_item->id);
            $target_zone_item->save();
        }
        if ($result) {
            DB::commit();
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.target_zones.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @throws GeneralException
     * @return bool
     */
    public function delete($target_zone)
    {
        $target_zone->items()->delete();
        if ($target_zone->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.target_zones.delete_error'));
    }
}
