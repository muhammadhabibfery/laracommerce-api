<?php

use App\Models\User;

/**
 * get a user where has admin or superadmin role
 *
 * @return User
 */
function getUserWithRole(string $role): User
{
    $user = User::query();
    if ($role === 'employee') {
        $user->whereJsonContains('role', 'ADMIN')
            ->orWhereJsonContains('role', 'SUPERADMIN');
    } elseif ($role === 'merchant') {
        $user->wherejsonContains('role', 'MERCHANT');
    } else {
        $user->whereJsonContains('role', 'CUSTOMER');
    }

    return $user->inRandomOrder()
        ->first();
}
