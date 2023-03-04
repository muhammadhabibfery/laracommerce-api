<?php

namespace App\Filament\Resources;

use App\Models\Order;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\OrderResource\Pages;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Admin Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer Name')
                    ->searchable(),
                TextColumn::make('invoice_number')
                    ->searchable(),
                TextColumn::make('total_price')
                    ->formatStateUsing(fn (int $state): string => currencyFormat($state)),
                BadgeColumn::make('status')
                    ->enum([
                        'SUCCESS' => 'Success',
                        'FAILED' => 'Failed',
                        'PENDING' => 'Pending',
                        'IN_CART' => 'In Cart'
                    ])
                    ->colors([
                        'success' => 'SUCCESS',
                        'danger' => 'FAILED',
                        'primary' => 'PENDING',
                        'secondary' => 'IN_CART'
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'SUCCESS' => 'Success',
                        'FAILED' => 'Failed',
                        'PENDING' => 'Pending',
                        'IN_CART' => 'In Cart'
                    ])
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
