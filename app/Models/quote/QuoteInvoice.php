<?php

namespace App\Models\quote;

use App\Models\invoice\Invoice;
use Illuminate\Database\Eloquent\Model;

class QuoteInvoice extends Model
{
    protected $table = 'quote_invoices';

    protected $fillable = ['quote_id', 'invoice_id'];

    public $timestamps = false;

    /**
     * Relations
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
