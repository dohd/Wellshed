<?php

namespace App\Models\purchaseorder;

use App\Models\hrm\Hrm;
use Illuminate\Database\Eloquent\Model;

class PurchaseorderReview extends Model
{

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'purchase_order_reviews';

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

        static::creating(function ($instance) {
            $instance->tid = PurchaseorderReview::max('tid')+1;
            $instance->user_id = auth()->user()->id ?: 0;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }

    public function purchaseorder()
    {
        return $this->belongsTo(Purchaseorder::class, 'purchase_order_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseorderReviewItem::class, 'purchase_order_review_id');
    }

    public function review_by()
    {
        return $this->belongsTo(Hrm::class, 'user_id');
    }

    public function review_docs()
    {
        return $this->hasMany(PurchaseorderReviewDoc::class, 'purchase_order_review_id');
    }
}
