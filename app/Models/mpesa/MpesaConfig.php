<?php

namespace App\Models\mpesa;

use App\Models\ModelTrait;
use App\Models\mpesa\Traits\MpesaConfigAttribute;
use Illuminate\Database\Eloquent\Model;

class MpesaConfig extends Model
{
    use ModelTrait, MpesaConfigAttribute;

    protected $table = 'mpesa_configs';

    protected $fillable = [
        'env',
        'type',
        'consumer_key',
        'consumer_secret',
        'shortcode',
        'head_office_shortcode',
        'initiator_name',
        'initiator_password_enc',
        // 'security_credential',
        // 'command_id',
        'result_url',
        'timeout_url',
        'validation_url',
        'confirmation_url',
        'passkey',
        'account_reference',
        'callback_url',
        'cert_path'
    ];

    protected $casts = [
        'last_token_expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($instance) {
            $instance->ins = auth()->user()->ins;
            return $instance;
        });
        // static::addGlobalScope('ins', function ($builder) {
        //     $builder->where('ins', '=', auth()->user()->ins);
        // });
    }

    public static function forIns(string $ins): ?self
    {
        return static::where('ins', $ins)->where('type','b2c')->first();
    }
}
