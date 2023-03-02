<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UsersStatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        $user = $this->getUser();
        return [
            Card::make('Customers count', $user->where('role', ['CUSTOMER'])->count()),
            Card::make('Merchants count', $user->where('role', ['MERCHANT'])->count())
        ];
    }

    private function getUser(): Collection
    {
        return User::all();
    }
}
