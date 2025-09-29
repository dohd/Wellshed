<?php

namespace App\Repositories\Focus\deduction;

use DB;
use Carbon\Carbon;
use App\Models\deduction\Deduction;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class deductionRepository.
 */
class DeductionRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Deduction::class;

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
        $user = $input['user'];
        $inputs = $input['input'];
       // dd($user['ins']);
        // $assetissuance_items = $input['assetissuance_items'];
    
        $inputs = array_map(function ($v) use($user) {
            //dd($v);
            return array_replace($v, [
                'ins' => $user['ins'],
                'user_id' => $user['user_id'],
            ]);
        }, $inputs);
        

        foreach ($inputs as $inputs) {
            if($inputs['name']=="NHIF"){
                $inputs['deduction_id'] = '1';
            }elseif ($inputs['name']=="NSSF") {
                $inputs['deduction_id'] = '2';
            }else {
                $inputs['deduction_id'] = '3';
            }
            //dd($inputs);
            Deduction::insert($inputs);
        }
        DB::commit();
        if ($user) return $user;
       
        throw new GeneralException(trans('exceptions.backend.deductions.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Deduction $deduction
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Deduction $deduction, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($deduction->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.deductions.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param deduction $deduction
     * @throws GeneralException
     * @return bool
     */
    public function delete(Deduction $deduction)
    {
        if ($deduction->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.deductions.delete_error'));
    }
}
