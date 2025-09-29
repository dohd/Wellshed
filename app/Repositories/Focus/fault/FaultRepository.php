<?php

namespace App\Repositories\Focus\fault;

use DB;
use Carbon\Carbon;
use App\Models\fault\Fault;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class faultRepository.
 */
class FaultRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Fault::class;

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
        if (Fault::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.faults.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param fault $fault
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Fault $fault, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($fault->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.faults.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param fault $fault
     * @throws GeneralException
     * @return bool
     */
    public function delete(Fault $fault)
    {
        if ($fault->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.faults.delete_error'));
    }
}
