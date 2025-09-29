<?php

namespace App\Models\project;

use App\Models\ModelTrait;
use App\Models\project\Traits\MileStoneItemRelationship;
use Illuminate\Database\Eloquent\Model;

class MileStoneItem extends Model
{
    use ModelTrait,
        MileStoneItemRelationship {
    }
    protected $table = 'milestone_items';

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

    
}
