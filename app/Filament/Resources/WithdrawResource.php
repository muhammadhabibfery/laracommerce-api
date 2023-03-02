<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Finance;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Facades\Filament;
use App\Policies\FinancePolicy;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\WithdrawResource\Pages;

class WithdrawResource extends Resource
{
    protected static ?string $model = Finance::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $pluralModelLabel = 'Withdraw';

    protected static ?string $slug = 'withdraw';

    protected static ?string $recordRouteKeyName = 'id';

    protected static ?string $navigationLabel = 'Withdraw';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes()
            ->where('user_id', '!=', auth()->id())
            ->where('description', 'LIKE', 'withdraw%');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('user_name')
                        ->label('User Name')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('user_merchantAccount_name')
                        ->label('Merchant Account Name')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('amount')
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            $component->state(currencyFormat($state));
                        })
                        ->disabled()
                        ->dehydrated(false),
                    Radio::make('is_confirm')
                        ->required()
                        ->in(['0', '1'])
                        ->options([
                            false => 'Reject',
                            true => 'Approve'
                        ])
                        ->label('Confirmation')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User Name'),
                TextColumn::make('user.merchantAccount.name')
                    ->label('Merchant Account Name'),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (int $state): string => currencyFormat($state)),
                BadgeColumn::make('status')
                    ->enum([
                        'SUCCESS' => 'Success',
                        'REJECT' => 'Reject',
                        'PENDING' => 'Pending'
                    ])
                    ->colors([
                        'success' => 'SUCCESS',
                        'danger' => 'REJECT',
                        'primary' => 'PENDING'
                    ]),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'SUCCESS' => 'Success',
                        'REJECT' => 'Reject',
                        'PENDING' => 'Pending'
                    ])
                    ->default('PENDING')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdraws::route('/'),
            'edit' => Pages\EditWithdraw::route('/{record:id}/edit'),
        ];
    }

    public static function registerNavigationItems(): void
    {
        if (!setAuthorization(auth()->user(), FinancePolicy::ADMIN_ROLE, FinancePolicy::STAFF_ROLE)) return;

        Filament::registerNavigationItems(static::getNavigationItems());
    }
}
