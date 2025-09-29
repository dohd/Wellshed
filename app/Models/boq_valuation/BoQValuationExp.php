<?php

namespace App\Models\boq_valuation;

use App\Models\casual_labourer_remuneration\CasualLabourersRemuneration;
use App\Models\items\PurchaseItem;
use App\Models\product\ProductVariation;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use Illuminate\Database\Eloquent\Model;

class BoQValuationExp extends Model
{
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'boq_valuation_exps';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [];

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Relationships 
     */
    public function casualRemun()
    {
        return $this->belongsTo(CasualLabourersRemuneration::class, 'casual_remun_id');
    }

    public function milestone()
    {
        return $this->belongsTo(ProjectMileStone::class, 'budget_line_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'expitem_id');
    }

    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class, 'productvar_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
