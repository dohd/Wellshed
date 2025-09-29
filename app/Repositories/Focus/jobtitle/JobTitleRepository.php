<?php

namespace App\Repositories\Focus\jobtitle;

use DB;
use Carbon\Carbon;
use App\Models\jobtitle\JobTitle;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class jobtitleRepository.
 */
class JobTitleRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = JobTitle::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get(['id','name','department','created_at']);
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
        $input = array_map( 'strip_tags', $input);
        if (JobTitle::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.jobtitles.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param jobtitle $jobtitle
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(JobTitle $jobtitle, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($jobtitle->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.jobtitles.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param jobtitle $jobtitle
     * @throws GeneralException
     * @return bool
     */
    public function delete(JobTitle $jobtitle)
    {
        if ($jobtitle->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.jobtitles.delete_error'));
    }
}
