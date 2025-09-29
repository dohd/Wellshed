<?php

namespace App\Models\quote\Traits;

/**
 * Class QuoteAttribute.
 */
trait QuoteAttribute
{
    // Make your attributes functions here
    // Further, see the documentation : https://laravel.com/docs/5.4/eloquent-mutators#defining-an-accessor


    /**
     * Action Button Attribute to show in grid
     * @return string
     */
    public function getActionButtonsAttribute()
    {
        return $this->getViewButtonAttribute("manage-quote", "biller.quotes.show");
    }
}
