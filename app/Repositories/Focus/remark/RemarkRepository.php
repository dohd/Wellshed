<?php

namespace App\Repositories\Focus\remark;

use App\Models\remark\Remark;
use App\Exceptions\GeneralException;
use App\Models\items\Prefix;
use App\Models\prospect\Prospect;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class RemarkRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Remark::class;

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
    public function create(array $input)
    {

        $data = $input['data'];
        $result = Remark::create($data);
        $temperate = $input['temperate'];

        $prospect = Prospect::find($data['prospect_id']);
        $prospect->temperate = $temperate['temperate'];
        $prospect->update();
        $response = $result->fresh();
        return $response;

        throw new GeneralException('Error Creating Remark');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param \App\Models\Remark $remark
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    // public function update(array $data)
    // {
    //     DB::beginTransaction();
        
    //     $result = $remark->update($data);
    //     if ($result) {
    //         DB::commit();
    //         return true;
    //     }

    //     throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    // }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\remark\Remark $remark
     * @throws GeneralException
     * @return bool
     */
    public function delete(Remark $remark)
    {
        $prefix = Prefix::where('note', 'remark')->first();
        $tid = gen4tid("{$prefix}-", $remark->reference);

        if ($remark->djcs->count()) 
            throw ValidationException::withMessages(["{$tid} is attached to DJC Report!"]);
        if ($remark->quotes->count()) 
            throw ValidationException::withMessages(["{$tid} is attached to Quote!"]);
            
        if ($remark->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}