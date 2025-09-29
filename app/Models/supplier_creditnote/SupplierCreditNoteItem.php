<?php

namespace App\Models\supplier_creditnote;

use App\Models\supplier_creditnote\Traits\SupplierCreditNoteItemRelationship;
use Illuminate\Database\Eloquent\Model;

class SupplierCreditNoteItem extends Model
{
    use SupplierCreditNoteItemRelationship;
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'supplier_credit_note_items';

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
