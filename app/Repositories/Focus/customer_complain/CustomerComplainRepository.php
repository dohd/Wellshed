<?php

namespace App\Repositories\Focus\customer_complain;

use DB;
use Carbon\Carbon;
use App\Models\customer_complain\CustomerComplain;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerComplainRepository.
 */
class CustomerComplainRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = CustomerComplain::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        $q->with('client_feedback');
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
        // dd($input);
        if (CustomerComplain::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.CustomerComplains.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param CustomerComplain $CustomerComplain
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($customer_complain, array $input)
    {
    	if ($customer_complain->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.customer_complains.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param CustomerComplain $CustomerComplain
     * @throws GeneralException
     * @return bool
     */
    public function delete($customer_complain)
    {
        if ($customer_complain->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.customer_complains.delete_error'));
    }
}
