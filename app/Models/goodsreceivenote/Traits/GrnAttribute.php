<?php

namespace App\Models\goodsreceivenote\Traits;

/**
 * Class ProductcategoryAttribute.
 */
trait GrnAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-grn", "biller.goodsreceivenote.show") . ' ' 
            . $this->getEditButtonAttribute("edit-grn", "biller.goodsreceivenote.edit") . ' ' 
            . $this->getDeleteButtonAttribute("delete-grn", "biller.goodsreceivenote.destroy");
    }
}
