<?php

namespace App\Models\rfq;

use App\Models\account\Account;
use App\Models\ModelTrait;
use App\Models\product\ProductVariation;
use App\Models\project\Project;
use App\Models\rfq\Traits\RfQAttribute;
use App\Models\rfq\Traits\RfQRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfQ extends Model
{
    use ModelTrait, RfQAttribute, RfQRelationship;
    protected $table = 'rfqs';

    protected $fillable = [];

    protected $attributes = [];


    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $guarded = [
        'id'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });


        static::addGlobalScope('ins', function ($builder) {
            $builder->where('rfqs.ins', '=', auth()->user()->ins);
        });
    }


    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariation::class, 'rfq_items', 'rfq_id', 'product_id');
    }

    public function expenses(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'rfq_items', 'rfq_id', 'expense_account_id');
    }

    public function items(): HasMany {

        return $this->hasMany(RfQItem::class, 'rfq_id', 'id');
    }

    public function project(): BelongsTo{

        return $this->belongsTo(Project::class, 'project_id', 'id');
    }



}
