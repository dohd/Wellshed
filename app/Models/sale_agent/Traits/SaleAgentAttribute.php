<?php

namespace App\Models\sale_agent\Traits;

/**
 * Class sale_agentAttribute.
 */
trait SaleAgentAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-sale_agent", "biller.sale_agents.show").'
                '.$this->getEditButtonAttribute("edit-sale_agent", "biller.sale_agents.edit").'
                '.$this->getDeleteButtonAttribute("delete-sale_agent", "biller.sale_agents.destroy").'
                ';
    }
}
