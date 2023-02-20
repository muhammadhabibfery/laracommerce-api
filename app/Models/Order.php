<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the user that owns the order
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The products that belong to the order
     *
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['quantity', 'total_price'])
            ->withTimestamps();
    }

    /**
     * get the total price products that belong to the order
     *
     * @return BelongsToMany
     */
    public function totalPriceProductsMerchant(): BelongsToMany
    {
        return $this->products();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'invoice_number';
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

        if (request()->is('api/merchant/*')) $result->whereHas('products', fn ($query) => $query->where('merchant_account_id', request()->user()->merchantAccount->id));

        if (request()->is('api/orders*')) $result->where('user_id', request()->user()->id);

        return $result->firstOrFail();
    }
}
