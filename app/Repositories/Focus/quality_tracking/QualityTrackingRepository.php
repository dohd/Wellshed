<?php

namespace App\Repositories\Focus\quality_tracking;

use App\Exceptions\GeneralException;
use App\Models\quality_tracking\QualityTracking;
use App\Repositories\BaseRepository;

class QualityTrackingRepository extends BaseRepository
{
    const MODEL = QualityTracking::class;

    public function getForDataTable()
    {

        return $this->query()->get();
    }

    public function create(array $input)
    {
        if (QualityTracking::create($input)) {
            return true;
        }
        throw new GeneralException('Error creating quality objective');
    }

    public function update($qualityObjective, array $input)
    {
         
        if ($qualityObjective->update($input))
            return true;

        throw new GeneralException('Error updating quality objective');
    }

}
