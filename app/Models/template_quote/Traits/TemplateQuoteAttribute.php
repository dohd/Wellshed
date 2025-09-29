<?php

namespace App\Models\template_quote\Traits;

trait TemplateQuoteAttribute
{
    public function getActionButtonsAttribute()
    {
        return '
         '.$this->getViewButtonAttribute("manage-quote", "biller.template-quotes.show").'
                '.$this->getEditButtonAttribute("edit-quote", "biller.template-quotes.edit").'
                '.$this->getDeleteButtonAttribute("delete-quote", "biller.template-quotes.destroy").'
                ';
    }
}
