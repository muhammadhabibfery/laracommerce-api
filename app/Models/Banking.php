<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public function merchantAccounts(): HasMany
    {
        return $this->hasMany(MerchantAccount::class);
    }

    /**
     * set the banking's name
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = str($value)->title();
    }

    /**
     * set the banking's alias
     *
     * @param string $value
     * @return void
     */
    public function setAliasAttribute($value)
    {
        $this->attributes['alias'] = strlen($value) > 3 ? str($value)->lower()->ucfirst() : str($value)->upper();
    }
}
