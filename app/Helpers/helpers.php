<?php

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

/**
 * Identify the user role
 *
 * @param  array $availableRoles
 * @param  array $userRole
 * @return bool
 */
function checkRole(array $availableRoles, array $userRole): bool
{
    return in_array(head($userRole), $availableRoles);
}

/**
 * transform Date Format
 *
 * @param  string $data
 * @param  string $format
 * @return string|Carbon
 */
function transformDateFormat(string $data, ?string $format = null): string|Carbon
{
    $result = Carbon::parse($data);

    if ($format) $result = $result->translatedFormat($format);

    return $result;
}

/**
 * set and display currency format
 *
 * @param  int $value
 * @return string
 */
function currencyFormat(int $value): string
{
    return "Rp. " .  number_format($value, 0, '.', '.');
}

/**
 * set and display integer format
 *
 * @param  string $value
 * @return int
 */
function integerFormat(string $value): int
{
    return (int) str_replace('.', '', $value);
}

/**
 * Query to get random categories and limit by 6.
 *
 * @return Collection
 */
function getRandomCategories(): Collection
{
    return Category::inRandomOrder()
        ->limit(6)
        ->get();
}

/**
 * Query to get top seller products.
 *
 * @return LengthAwarePaginator
 */
function getTopSellerProducts(): LengthAwarePaginator
{
    return Product::where('sold', '>', 3)
        ->latest()
        ->paginate(6)
        ->appends(request()->query());
}
