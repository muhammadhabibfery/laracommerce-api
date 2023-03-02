<?php

namespace App\Policies;

use App\Models\Banking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BankingPolicy
{
    use HandlesAuthorization;

    private const STAFF_ROLE = 'STAFF', ADMIN_ROLE = 'ADMIN';

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return setAuthorization($user, self::ADMIN_ROLE, self::STAFF_ROLE);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Banking  $banking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Banking $banking)
    {
        return setAuthorization($user, self::ADMIN_ROLE, self::STAFF_ROLE);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return setAuthorization($user, self::ADMIN_ROLE, self::STAFF_ROLE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Banking  $banking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Banking $banking)
    {
        return setAuthorization($user, self::ADMIN_ROLE, self::STAFF_ROLE) && setAbility(fn (): bool => $banking->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Banking  $banking
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Banking $banking)
    {
        return setAuthorization($user, self::ADMIN_ROLE, self::STAFF_ROLE) && setAbility(fn (): bool => $banking->created_by === $user->id);
    }
}
