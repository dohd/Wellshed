<?php

namespace App\Repositories\Focus\jobschedule;

use DB;
use Carbon\Carbon;
use App\Models\jobschedule\Jobschedule;
use App\Models\jobschedule\JobscheduleRelation;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MiscRepository.
 */
class JobscheduleRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Jobschedule::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
       
        return
            $q->get(['id','project_id','client_id','expected_end_date','status']);
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

         $input['expected_start_date']=date_for_database($input['expected_start_date']);
         $date=date_for_database($input['expected_start_date']);
        $dt = Carbon::create($input['expected_start_date']);
        $dt->addDays($input['duration']);

        $region = @$input['region_id'];

        unset($input['region_id']);

        $input['expected_end_date']=date_for_database($dt);


        

         $input = array_map( 'strip_tags', $input);
         $results=Jobschedule::create($input);
        if ($results) {

             if (is_array($region)) {
                $region_group = array();
                foreach ($region as $row) {
                    $tag_group[] = array('project_id' => $input['project_id'], 'section_id' => $results->id, 'region_id' => $row);
                   
                }
            }

            JobscheduleRelation::insert($tag_group);
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.jobschedule.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Misc $misc
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Jobschedule $jobschedule, array $input)
    {
        $input = array_map( 'strip_tags', $input);
        if ($misc->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.jobschedule.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Misc $misc
     * @throws GeneralException
     * @return bool
     */
    public function delete(Jobschedule $jobschedule)
    {
        if ($misc->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.jobschedule.delete_error'));
    }
}
