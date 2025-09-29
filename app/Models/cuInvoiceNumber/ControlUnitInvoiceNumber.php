<?php

namespace App\Models\cuInvoiceNumber;

use Illuminate\Database\Eloquent\Model;

class ControlUnitInvoiceNumber extends Model
{

    protected $table = 'control_unit_invoice_numbers';

    protected $primaryKey = 'cuin_number';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'cu_no',
        'ins'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->fill([

                'ins' => auth()->user()->ins,
            ]);
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            if (isset(auth()->user()->ins)) {
                $builder->where('ins', auth()->user()->ins);
            }
        });
    }


}
