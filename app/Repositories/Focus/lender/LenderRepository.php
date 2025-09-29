<?php

namespace App\Repositories\Focus\lender;


use App\Models\lender\Lender;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;


/**
 * Class BankRepository.
 */
class LenderRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Lender::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get(['id','name','contact']);
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
        if (Lender::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.banks.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Lender $lender
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Lender $lender, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($lender->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.banks.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Lender $lender
     * @throws GeneralException
     * @return bool
     */
    public function delete(Lender $lender)
    {
        if ($lender->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.banks.delete_error'));
    }
}
