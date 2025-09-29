<?php

namespace App\Repositories\Focus\assetequipment;

use App\Models\assetequipment\Assetequipment;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class ProductcategoryRepository.
 */
class AssetequipmentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Assetequipment::class;

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
        // dd($input);
        foreach ($input as $key => $value) {
            if (in_array($key, ['purchase_date', 'warranty_expiry_date'], 1)) {
                if ($value) $input[$key] = date_for_database($value);
            }
        }

        $result = Assetequipment::create($input);        
        if ($result) return $result;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Assetequipment $assetequipment, array $input)
    {
        // dd($input);
        foreach ($input as $key => $value) {
            if (in_array($key, ['purchase_date', 'warranty_expiry_date'], 1)) {
                if ($value) $input[$key] = date_for_database($value);
            }
        }
    	if ($assetequipment->update($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.assetequipments.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($assetequipment)
    {
        if ($assetequipment->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.assetequipments.delete_error'));
    }
}
