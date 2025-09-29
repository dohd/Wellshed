<?php

namespace App\Models\template_quote\Traits;

use App\Models\product\ProductVariation;
use App\Models\template_quote\TemplateQuote;

trait TemplateQuoteItemRelationship
{
    // public function invoice()
    // {
    //     return $this->belongsTo(Invoice::class);
    // }

    public function templateQuote()
    {
        return $this->belongsTo(TemplateQuote::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id')->withoutGlobalScopes();
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id')->withoutGlobalScopes();
    }
}
