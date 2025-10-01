<?php

namespace App\Repositories\Focus\orders;

use DB;
use Carbon\Carbon;
use App\Exceptions\GeneralException;
use App\Models\orders\Orders;
use App\Repositories\BaseRepository;

/**
 * Class OrdersRepository.
 */
class OrdersRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Orders::class;

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
        if (Orders::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.Orderss.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Orders $Orders
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($orders, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($orders->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.Orderss.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Orders $Orders
     * @throws GeneralException
     * @return bool
     */
    public function delete($orders)
    {
        if ($orders->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.Orderss.delete_error'));
    }
}
