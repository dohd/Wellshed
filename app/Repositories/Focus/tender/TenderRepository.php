<?php

namespace App\Repositories\Focus\tender;

use DB;
use Carbon\Carbon;
use App\Models\tender\Tender;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TenderRepository.
 */
class TenderRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Tender::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->get();
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
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'submission_date','site_visit_date']))
                $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'bid_bond_amount']))
                $input[$key] = numberClean($val);
        }  
        if (Tender::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.tenders.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param tender $tender
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($tender, array $input)
    {
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'submission_date','site_visit_date']))
                $input[$key] = date_for_database($val);
            if (in_array($key, ['amount', 'bid_bond_amount']))
                $input[$key] = numberClean($val);
        }  
    if ($tender->update($input))
            return true;

        throw new GeneralException('Error Up');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param tender $tender
     * @throws GeneralException
     * @return bool
     */
    public function delete($tender)
    {
        if ($tender->delete()) {
            return true;
        }

        throw new GeneralException('Error Deleting Tender');
    }
}
