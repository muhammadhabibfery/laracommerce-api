<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Finance;
use Filament\Resources\Table;
use Filament\Facades\Filament;
use App\Policies\FinancePolicy;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\FinanceResource\Pages;

class FinanceResource extends Resource
{
    protected static ?string $model = Finance::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Admin Management';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes()
            ->where('user_id', auth()->id())
            ->where('status', 'SUCCESS');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id'),
                TextColumn::make('description'),
                TextColumn::make('type'),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state): string => currencyFormat($state)),
                TextColumn::make('balance')
                    ->formatStateUsing(fn (int $state): string => currencyFormat($state)),
                TextColumn::make('created_at')
                    ->formatStateUsing(fn (Carbon $state): string => $state->format('l, ' . config('app.date_format'))),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinances::route('/'),
        ];
    }

    public static function registerNavigationItems(): void
    {
        if (!setAuthorization(auth()->user(), FinancePolicy::ADMIN_ROLE)) return;

        Filament::registerNavigationItems(static::getNavigationItems());
    }
}
