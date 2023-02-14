<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
// use Laravel\Scout\Attributes\SearchUsingFullText;
// use Laravel\Scout\Attributes\SearchUsingPrefix;

class Product extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    // #[SearchUsingPrefix(['name'])]
    // #[SearchUsingFullText(['description'])]
    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    /**
     * Get the product images for the product
     *
     * @return HasMany
     */
    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the merchant account that owns the product
     *
     * @return BelongsTo
     */
    public function merchantAccount(): BelongsTo
    {
        return $this->belongsTo(MerchantAccount::class);
    }

    /**
     * Get the category that owns the product
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)
            ->withPivot(['quantity', 'total_price'])
            ->withTimestamps();
    }

    /**
     * set the product's name
     *
     * @param  string $value
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = str($value)->title()->value();
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

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null): Model|null
    {
        $result = $this->where($this->getRouteKeyName(), $value);

        if (request()->is('api/merchant/*')) $result->where('merchant_account_id', request()->user()->merchantAccount->id);

        return $result->firstOrFail();
    }
}
