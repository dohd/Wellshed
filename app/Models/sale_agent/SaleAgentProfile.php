<?php

namespace App\Models\sale_agent;

use App\Models\sale_agent\Traits\SaleAgentProfileRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SaleAgentProfile extends Model
{
    use SaleAgentProfileRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'sales_agent_profiles';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'sale_agent_id','headline','bio','skills','experience','education','cv_path',
        'linkedin_url','portfolio_url','availability','hourly_rate','preferred_categories','extra',
        'employment_status','professional_courses','describe_yourself','facebook_url',
        'tiktok_url','instagram_url','twitter_url',
    ];


    protected $casts = [
        'skills' => 'array',
        'experience' => 'array',
        'education' => 'array',
        'professional_courses' => 'array',
        'preferred_categories' => 'array',
        'extra' => 'array',
    ];

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
    

}
