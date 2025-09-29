<?php

namespace App\Repositories\Focus\projectequipment;

use DB;
use Carbon\Carbon;
use App\Models\projectequipment\Projectequipment;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Mavinoo\LaravelBatch\LaravelBatchFacade as Batch;

/**
 * Class ProductcategoryRepository.
 */
class ProjectequipmentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Projectequipment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        
       $q=$this->query();
      
        $q->when(request('rel_id'), function ($q) {
            return $q->where('schedule_id', '=',request('rel_id',0));
       });
        $q->when(request('job_card')==1, function ($q) {
            return $q->whereNull('job_card');
       });
        $q->when(request('job_card')==2, function ($q) {
            return $q->whereNotNull('job_card');
       });

       return $q->get();

       
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

      $inser_value = array();
        DB::beginTransaction();

        if($input['data']['action']==1){

            foreach ($input['data']['equipment_id'] as $key => $value) {

                  $inser_value[] = array(

                    'ins' => $input['data']['ins'],
                    'schedule_id' => strip_tags(@$input['data']['shedule_id']),
                    'client_id' => strip_tags(@$input['data']['client_id']),
                    'project_id' => strip_tags(@$input['data']['project_id']),
                    'equipment_id' => $value,

                     );




             }
             //dd($inser_value);

               Projectequipment::insert($inser_value);



        }
 

       DB::commit();

        $returnvalue=$input['data']['shedule_id'];
     return $returnvalue;
        throw new GeneralException('Error Creating Projectequipment');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Projectequipment $projectequipment, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($equipment->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.Projectequipment.update_error'));
    }


     public function job_card(array $input)
    {

        $inser_value = array();
        DB::beginTransaction();

        if($input['data']['action']==1){

            foreach ($input['data']['equipment_id'] as $key => $value) {

                  $inser_value[] = array(
                    'id' => $value,
                    'ins' => $input['data']['ins'],
                    'done_by' => $input['data']['done_by'],
                    'technician' => strip_tags(@$input['data']['technician']),
                    'job_card' => strip_tags(@$input['data']['job_card']),
                    'recommendation' => strip_tags(@$input['data']['recommendation']),
                    'job_date' => date_for_database(@$input['data']['job_date']),
                      );




             }


             $update_job_card= new Projectequipment;
            $index = 'id';
            Batch::update($update_job_card, $inser_value, $index);



        }
 

       DB::commit();
         return true;
        throw new GeneralException('Error Updateing JobCard');

    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Projectequipment $projectequipment)
    {
        if ($equipment->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.Projectequipment.delete_error'));
    }
}
