<?php

namespace App\Policies;

use App\Models\Finance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancePolicy
{
    use HandlesAuthorization;

    public const ADMIN_ROLE = 'ADMIN', STAFF_ROLE = 'STAFF';


    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return request()->is('admin/withdraw*')
            ? setAuthorization($user, self::ADMIN_ROLE, self::STAFF_ROLE)
            :  setAuthorization($user, self::ADMIN_ROLE);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Finance  $finance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Finance $finance)
    {
        return setAuthorization($user, self::ADMIN_ROLE, self::STAFF_ROLE) && setAbility(fn (): bool => $finance->status === 'PENDING');
    }
}
