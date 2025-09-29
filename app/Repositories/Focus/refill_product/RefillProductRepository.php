<?php

namespace App\Repositories\Focus\refill_product;

use App\Exceptions\GeneralException;
use App\Models\refill_product\RefillProduct;
use App\Repositories\BaseRepository;

class RefillProductRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = RefillProduct::class;

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
     * @return RefillProduct $refill_product
     */
    public function create(array $input)
    {
        // dd($input);
        $input['unit_price'] = numberClean($input['unit_price']);
        $result = RefillProduct::create($input);
        return $result;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param RefillProduct $refill_product
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(RefillProduct $refill_product, array $input)
    {
        // dd($input);
        $input['unit_price'] = numberClean($input['unit_price']);
        return $refill_product->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param RefillProduct $refill_product
     * @throws GeneralException
     * @return bool
     */
    public function delete(RefillProduct $refill_product)
    {
        return $refill_product->delete();
    }
}
