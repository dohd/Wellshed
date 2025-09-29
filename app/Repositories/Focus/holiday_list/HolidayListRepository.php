<?php

namespace App\Repositories\Focus\holiday_list;

use DB;
use App\Exceptions\GeneralException;
use App\Models\holiday_list\HolidayList;
use App\Repositories\BaseRepository;

class HolidayListRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = HolidayList::class;

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
     * @return HolidayList $holiday_list
     */
    public function create(array $input)
    {
        // dd($input);

        $input['date'] = date_for_database($input['date']);
        $result = HolidayList::create($input);

        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.HolidayLists.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param HolidayList $holiday_list
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(HolidayList $holiday_list, array $input)
    {
        // dd($input);
        $input['date'] = date_for_database($input['date']);
        if ($holiday_list->update($input)) return $holiday_list;

        throw new GeneralException(trans('exceptions.backend.HolidayLists.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param HolidayList $holiday_list
     * @throws GeneralException
     * @return bool
     */
    public function delete(HolidayList $holiday_list)
    {
        if ($holiday_list->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.HolidayLists.delete_error'));
    }
}
