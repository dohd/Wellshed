<?php

namespace App\Repositories\Focus\appraisal_type;

use DB;
use Carbon\Carbon;
use App\Models\appraisal_type\AppraisalType;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppraisalTypeRepository.
 */
class AppraisalTypeRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = AppraisalType::class;

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
        if (AppraisalType::create($input)) {
            return true;
        }
        throw new GeneralException('Error Creating the Appraisal Type');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($appraisal_type, array $input)
    {
    	if ($appraisal_type->update($input))
            return true;

        throw new GeneralException('Error Updating the Appraisal Type');
    }

    /**
     * For deleting the respective model from storage
     *
     * @throws GeneralException
     * @return bool
     */
    public function delete($appraisal_type)
    {
        if ($appraisal_type->delete()) {
            return true;
        }

        throw new GeneralException('Error Deleting the Appraisal Type');
    }
}
