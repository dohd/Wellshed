<?php

namespace App\Repositories\Focus\job_category;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\job_category\JobCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class JobCategoryRepository.
 */
class JobCategoryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = JobCategory::class;

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
        if (JobCategory::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.job_categories.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param job_category $job_category
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($job_category, array $input)
    {
    	if ($job_category->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.job_categories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param job_category $job_category
     * @throws GeneralException
     * @return bool
     */
    public function delete($job_category)
    {
        if ($job_category->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.job_categories.delete_error'));
    }
}
