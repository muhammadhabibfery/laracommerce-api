<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the products for the merchant account
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the coupons for the merchant account
     *
     * @return HasMany
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Get the user that owns the merchant account
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the banking that owns the merchant account
     *
     * @return BelongsTo
     */
    public function banking(): BelongsTo
    {
        return $this->belongsTo(Banking::class);
    }

    /**
     * set the merchant account's name and slug.
     *
     * @param  string $value
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = str($value)->title()->value();
        $this->attributes['slug'] = str($value)->slug()->value();
    }

    /**
     * set the bank account's name.
     *
     * @param  string $value
     * @return void
     */
    public function setBankAccountNameAttribute(string $value): void
    {
        $this->attributes['bank_account_name'] = str($value)->title()->value();
    }

    /**
     * get product image's name as file
     *
     * @return mixed
     */
    public function getImage()
    {
        return $this->image ? asset('storage/merchant-images/' . $this->image) : asset('images/no-image.png');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
