<?php

namespace App\Models\account;

use App\Models\account\Traits\AccountTypeRelationship;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use AccountTypeRelationship;
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'account_types';

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
