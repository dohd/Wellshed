<?php

namespace App\Repositories\Focus\allowance;

use DB;
use Carbon\Carbon;
use App\Models\allowance\Allowance;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AllowanceRepository.
 */
class AllowanceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Allowance::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get(['id','name','type','is_taxable','created_at']);
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
        if (Allowance::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.departments.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Allowance $allowance
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Allowance $allowance, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($allowance->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.allowance.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Allowance $allowance
     * @throws GeneralException
     * @return bool
     */
    public function delete(Allowance $allowance)
    {
        if ($allowance->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.allowance.delete_error'));
    }
}
