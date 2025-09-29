<?php

namespace App\Repositories\Focus\equipmentcategory;

use App\Models\equipmentcategory\EquipmentCategory;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class ProductcategoryRepository.
 */
class EquipmentCategoryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = EquipmentCategory::class;

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
     * @return bool
     */
    public function create(array $input)
    {
        $input = array_map('strip_tags', $input);
        $c = EquipmentCategory::create($input);
        
        if ($c->id) return $c->id;

        throw new GeneralException('Error Creating EquipmentCategory');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(EquipmentCategory $equipmentcategory, array $input)
    {
        // dd($input);
        if ($equipmentcategory->update($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(EquipmentCategory $equipmentcategory)
    {
        if ($equipmentcategory->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
