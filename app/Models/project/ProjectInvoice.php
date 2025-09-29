<?php

namespace App\Models\project;

use App\Models\invoice\Invoice;
use Illuminate\Database\Eloquent\Model;

class ProjectInvoice extends Model
{
    protected $table = 'project_invoices';

    protected $fillable = ['project_id', 'invoice_id'];

    public $timestamps = false;

    /**
     * Relations
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
