<?php

namespace App\Models\promotions;

use App\Models\customer_complain\CustomerComplain;
use Illuminate\Database\Eloquent\Model;

class ClientFeedback extends Model
{
    protected $table = 'client_feedback';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'category',
        'details',
        'file_path',
        'company_id',
        'redeemable_uuid',
        'promo_code_id',
        'title',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('company_id', '=', auth()->user()->ins);
        });
    }

    public function promoCode()
    {
        return $this->belongsTo(PromotionalCode::class,'promo_code_id');
    }
    public function getFilePathsAttribute(): array
    {
        $raw = $this->file_path;

        if (empty($raw)) return [];

        // If it's valid JSON array, return it; otherwise treat as single path
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [$raw];
    }
}
