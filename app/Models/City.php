<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the users for the city
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the province that owns the city
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
