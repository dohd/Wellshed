<?php

namespace App\Repositories\Focus\refill_customer;

use App\Exceptions\GeneralException;
use App\Models\refill_customer\RefillCustomer;
use App\Repositories\BaseRepository;

class RefillCustomerRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = RefillCustomer::class;

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
     * @return RefillCustomer $refill_customer
     */
    public function create(array $input)
    {
        // dd($input);
        $result = RefillCustomer::create($input);
        return $result;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param RefillCustomer $refill_customer
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(RefillCustomer $refill_customer, array $input)
    {
        // dd($input);
        return $refill_customer->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param RefillCustomer $refill_customer
     * @throws GeneralException
     * @return bool
     */
    public function delete(RefillCustomer $refill_customer)
    {
        return $refill_customer->delete();
    }
}
