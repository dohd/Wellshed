<?php

namespace App\Repositories\Focus\leave_category;

use App\Exceptions\GeneralException;
use App\Models\leave_category\LeaveCategory;
use App\Repositories\BaseRepository;

class LeaveCategoryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = LeaveCategory::class;

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
     * @return LeaveCategory $leave_category
     */
    public function create(array $input)
    {
        // dd($input);
        $input['qty'] = numberClean($input['qty']);
        $result = LeaveCategory::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param LeaveCategory $leave_category
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(LeaveCategory $leave_category, array $input)
    {
        // dd($input);
        $input['qty'] = numberClean($input['qty']);
        if ($leave_category->update($input)) return $leave_category;

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param LeaveCategory $leave_category
     * @throws GeneralException
     * @return bool
     */
    public function delete(LeaveCategory $leave_category)
    {
        if ($leave_category->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
