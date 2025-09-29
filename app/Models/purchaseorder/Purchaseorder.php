<?php

namespace App\Models\purchaseorder;

use App\Models\Access\User\User;
use App\Models\ModelTrait;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\supplier\Supplier;
use Illuminate\Database\Eloquent\Model;
use App\Models\purchaseorder\Traits\PurchaseorderAttribute;
use App\Models\purchaseorder\Traits\PurchaseorderRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchaseorder extends Model
{
    use ModelTrait,
        PurchaseorderAttribute,
        PurchaseorderRelationship {
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'purchase_orders';

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
     * Constructor of Model
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }


    public function project(): BelongsTo{

        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function budgetLine(): BelongsTo{

        return $this->belongsTo(ProjectMileStone::class, 'project_milestone', 'id');
    }

    public function supplier(): BelongsTo{

        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function creator(): BelongsTo {

        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function statuses()
    {
        return $this->hasMany(PurchaseorderApproval::class, 'purchase_order_id');
    }

}
