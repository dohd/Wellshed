<?php

namespace App\Models\meta_whatsapp;

use Illuminate\Database\Eloquent\Model;

class MetaWhatsappThread extends Model
{

    protected $table = 'meta_whatsapp_threads';

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
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

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
        

    }

    // Relationships
    
}
