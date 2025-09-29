<?php

namespace App\Models\purchase;

use App\Models\Access\User\User;
use App\Models\ModelTrait;
use App\Models\project\Project;
use App\Models\project\ProjectMileStone;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;
use Illuminate\Database\Eloquent\Model;
use App\Models\purchase\Traits\PurchaseAttribute;
use App\Models\purchase\Traits\PurchaseRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use ModelTrait,
        PurchaseAttribute,
        PurchaseRelationship {
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'bills';

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

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
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


}
