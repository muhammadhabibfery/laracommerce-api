<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmails;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'array'
    ];

    /**
     * Get the merchant account associate with the user
     *
     * @return HasOne
     */
    public function merchantAccount(): HasOne
    {
        return $this->hasOne(MerchantAccount::class);
    }

    /**
     * Get the finance associate with the user
     *
     * @return HasOne
     */
    public function finance(): HasOne
    {
        return $this->hasOne(Finance::class);
    }

    /**
     * set the user's name
     *
     * @param  string $value
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = str($value)->title()->value();
    }

    /**
     * set the user's username
     *
     * @param  string $value
     * @return void
     */
    public function setUsernameAttribute(string $value): void
    {
        $this->attributes['username'] = strtolower($value);
    }

    /**
     * set the user's role
     *
     * @param  string $value
     * @return void
     */
    public function setRoleAttribute(string $value): void
    {
        $value = strtoupper($value);
        $this->attributes['role'] = json_encode([$value]);
    }

    /**
     * get the user's avatar with custom directory path
     *
     * @return mixed
     */
    public function getAvatar(): mixed
    {
        return $this->avatar ? asset('storage/avatars/' . $this->avatar) : asset('images/no-user.jpg');
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmails);
    }
}
