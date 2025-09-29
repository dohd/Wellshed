<?php

namespace App\Models\invoice;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\invoice\Traits\InvoiceAttribute;
use App\Models\invoice\Traits\InvoiceRelationship;

class Invoice extends Model
{
    use ModelTrait,
        InvoiceAttribute,
        InvoiceRelationship {
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'invoices';

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
            $instance->status = $instance->status ?: 'due';
            if (!$instance->is_imported && !$instance->man_journal_id) {
                $tid = Invoice::where('is_imported', 0)->whereNull('man_journal_id')->max('tid')+1;
                if (Invoice::where('is_imported', 1)->max('tid') == $tid) $tid += 1;
                $instance->tid = $tid;
            }
            $instance->user_id = auth()->user()->id ?: 0;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }
}
