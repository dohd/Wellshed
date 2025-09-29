<?php

namespace App\Models\promotions;

use App\Models\Company\Company;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class PromotionalCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'company_id',
        'description',
        'usage_limit',
        'reservation_period',
        'reservations_count',
        'res_limit_1',
        'res_limit_2',
        'res_limit_3',
        'used_count',
        'promo_type',
        'discount_type',
        'discount_value',
        'discount_value_2',
        'discount_value_3',
        'valid_from',
        'valid_until',
        'status',
        'currency_id',
        'commision_type',
        'cash_back_1',
        'cash_back_2',
        'cash_back_3',
        'description_promo',
        'flier_path',
        'caption',
        'total_commission_type',
        'total_commission',
        'company_commission',
        'cash_back_3_amount','cash_back_2_amount',
        'cash_back_1_amount','company_commission_amount',
        'company_commission_percent','cash_back_1_percent',
        'cash_back_2_percent','cash_back_3_percent'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {

            $instance->company_id = auth()->user()->ins;
            $instance->created_by = auth()->user()->id;
            return $instance;
        });

        static::addGlobalScope('company_id', function ($builder) {

            if (Auth::user()) $builder->where('company_id', Auth::user()->ins);
        });
    }


    /**
     * Get the company that owns the promotional code.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function productVariations()
    {
        return $this->belongsToMany(ProductVariation::class, 'promotional_code_products', 'promotional_code_id', 'product_variation_id')
            ->withTimestamps();
    }

    public function productCategories()
    {
        return $this->belongsToMany(Productcategory::class, 'promotional_code_product_categories', 'promotional_code_id', 'product_category_id')
            ->withTimestamps();
    }


    public function customersReservations(): HasMany {

        return $this->hasMany(CustomersPromoCodeReservation::class, 'promo_code_id', 'id');
    }

    public function thirdPartiesReservations(): HasMany {

        return $this->hasMany(ThirdPartiesPromoCodeReservation::class, 'promo_code_id', 'id');
    }

    public function referralReservations(): HasMany {

        return $this->hasMany(ReferralsPromoCodeReservation::class, 'promo_code_id', 'id');
    }


}
