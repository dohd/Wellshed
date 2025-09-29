<?php

namespace App\Models\promotions;

use App\Models\invoice\Invoice;
use App\Models\product\ProductVariation;
use Illuminate\Database\Eloquent\Model;

class InvoicePromotionalDiscountData extends Model
{
    // Define the table name explicitly (optional if it matches the class name in snake_case)
    protected $table = 'invoice_promotional_discount_data';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'invoice_id',
        'product_id',
        'name',
        'discount_type',
        'discount_offered',
        'price',
        'unit_discount',
        'quantity',
        'discount',
        'tax_rate',
        'discounted_tax',
    ];

    // Define relationships (if needed)

    // Example: Relationship with Invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Example: Relationship with Product
    public function product()
    {
        return $this->belongsTo(ProductVariation::class);
    }
}
