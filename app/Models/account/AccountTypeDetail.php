<?php

namespace App\Models\account;

use App\Models\account\Traits\AccountTypeDetailRelationship;
use Illuminate\Database\Eloquent\Model;

class AccountTypeDetail extends Model
{
    use AccountTypeDetailRelationship;
    
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'account_type_details';

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
