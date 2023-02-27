<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmails;
use App\Notifications\ResetPasswords;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword, FilamentUser, HasAvatar
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
     * The finances that belong to the user
     *
     * @return HasMany
     */
    public function finances(): HasMany
    {
        return $this->hasMany(Finance::class);
    }

    /**
     * The orders that belong to the user
     *
     * @return BelongsToMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the city that owns the user
     *
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
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
     * Get the user's avatar only for admin panel (user who has admin or staff role).
     *
     * @return string
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswords($token));
    }

    /**
     * User who has access to the admin panel
     *
     * @return bool
     */
    public function canAccessFilament(): bool
    {
        return checkRole(['ADMIN', 'STAFF'], auth()->user()->role) && auth()->user()->status === 'ACTIVE';
    }
}
