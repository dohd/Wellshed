<?php

namespace App\Models\assetequipment\Traits;

/**
 * Class CustomerAttribute.
 */
trait AssetequipmentAttribute
{
    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return  $this->getViewButtonAttribute("manage-asset-equipment", "biller.assetequipments.show")
        .' ' . $this->getEditButtonAttribute("edit-asset-equipment", "biller.assetequipments.edit")
        .' ' . $this->getDeleteButtonAttribute("delete-asset-equipment", "biller.assetequipments.destroy");
    }
}
