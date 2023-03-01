<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawResource\Pages;
use App\Models\Finance;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Radio;
use Filament\Tables\Filters\SelectFilter;

class WithdrawResource extends Resource
{
    protected static ?string $model = Finance::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $pluralModelLabel = 'Withdraw';

    protected static ?string $slug = 'withdraw';

    protected static ?string $recordRouteKeyName = 'id';

    protected static ?string $navigationLabel = 'Withdraw';

    protected static ?string $navigationGroup = 'Admin Management';

    protected static ?int $navigationSort = 3;

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
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdraws::route('/'),
            'edit' => Pages\EditWithdraw::route('/{record:id}/edit'),
        ];
    }
}
