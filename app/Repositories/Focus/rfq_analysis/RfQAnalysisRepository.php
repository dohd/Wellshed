<?php

namespace App\Repositories\Focus\rfq_analysis;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\rfq_analysis\RfQAnalysis;
use App\Repositories\BaseRepository;

/**
 * Class RfQAnalysisRepository.
 */
class RfQAnalysisRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = RfQAnalysis::class;

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
        $input = array_map( 'strip_tags', $input);
        if (RfQAnalysis::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.rfq_analysiss.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param rfq_analysis $rfq_analysis
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(RfQAnalysis $rfq_analysis, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($rfq_analysis->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.rfq_analysiss.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param rfq_analysis $rfq_analysis
     * @throws GeneralException
     * @return bool
     */
    public function delete(RfQAnalysis $rfq_analysis)
    {
        
        if ($rfq_analysis->supplier_items()->delete() && $rfq_analysis->items()->delete() && $rfq_analysis->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.rfq_analysiss.delete_error'));
    }
}
