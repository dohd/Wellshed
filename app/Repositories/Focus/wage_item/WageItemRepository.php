<?php

namespace App\Repositories\Focus\wage_item;

use App\Exceptions\GeneralException;
use App\Models\wage_item\WageItem;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class WageItemRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = WageItem::class;

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
    public function create(array $data)
    {
        foreach($data as $key => $val) {
            if (in_array($key, ['std_rate', 'weekday_ot', 'weekend_sat_ot', 'weekend_sun_ot', 'holiday_ot'])) {
                $data[$key] = numberClean($val);
            }
        }
        $exists = WageItem::where('earning_type', 'overtime')->exists();
        if ($exists && @$data['earning_type'] == 'overtime') {
            throw ValidationException::withMessages(['Earning Type already exists of `Overtime`']);
        }

        $wageItem = WageItem::create($data);

        return $wageItem;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\WageItem $wageItem
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(WageItem $wageItem, array $data)
    {
        foreach($data as $key => $val) {
            if (in_array($key, ['std_rate', 'weekday_ot', 'weekend_sat_ot', 'weekend_sun_ot', 'holiday_ot'])) {
                $data[$key] = numberClean($val);
            }
        }
        $exists = WageItem::where('id', '!=', $wageItem->id)->where('earning_type', 'overtime')->exists();
        if ($exists && @$data['earning_type'] == 'overtime') {
            throw ValidationException::withMessages(['Earning Type already exists of `Overtime`']);
        }

        return $wageItem->update($data);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\lead\WageItem $wageItem
     * @throws GeneralException
     * @return bool
     */
    public function delete(WageItem $wageItem)
    {
        return $wageItem->delete();
    }
}