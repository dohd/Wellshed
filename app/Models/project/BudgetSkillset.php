<?php

namespace App\Models\project;

use App\Models\project\Traits\BudgetSkillsetRelationship;
use Illuminate\Database\Eloquent\Model;

class BudgetSkillset extends Model
{
    use BudgetSkillsetRelationship;
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'budget_skillsets';

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
