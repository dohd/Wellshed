<?php

namespace App\Repositories\Focus\refill_product_category;

use App\Exceptions\GeneralException;
use App\Models\refill_product_category\RefillProductCategory;
use App\Repositories\BaseRepository;

class RefillProductCategoryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = RefillProductCategory::class;

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
     * @return RefillProductCategory $product_category
     */
    public function create(array $input)
    {
        // dd($input);
        $result = RefillProductCategory::create($input);
        return $result;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param RefillProductCategory $product_category
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(RefillProductCategory $product_category, array $input)
    {
        // dd($input);
        return $product_category->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param RefillProductCategory $product_category
     * @throws GeneralException
     * @return bool
     */
    public function delete(RefillProductCategory $product_category)
    {
        return $product_category->delete();
    }
}
