<?php

namespace App\Providers;

use App\Filament\Resources\BankingResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ProfileResource;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\ServiceProvider;
use App\Filament\Resources\UserResource;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale(config('app.locale'));

        Filament::serving(function () {
            Filament::registerNavigationGroups([
                'Admin Management',
                'Staff Management',
            ]);

            Filament::registerUserMenuItems([
                'account' => UserMenuItem::make()
                    ->label('Profile')
                    ->url(ProfileResource::getUrl()),
            ]);
        });
    }
}
