<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\City;
use App\Models\User;
use Filament\Tables;
use Filament\Pages\Page;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\CreateUser;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Admin Management';

    protected static ?string $recordRouteKeyName = 'username';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id', '!=', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('city_id')
                    ->options(fn () => City::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->required()
                    ->exists('cities', 'id'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(50)
                    ->reactive()
                    ->afterStateUpdated(
                        function (Closure $set, $state) {
                            return $set('username', function () use ($state) {
                                $state = str($state)->slug()->value();
                                return User::where('username', $state)->first() ? $state .= rand(11, 99) : $state;
                            });
                        }
                    ),
                TextInput::make('username')
                    ->maxLength(35)
                    ->disabled(),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->numeric()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->minLength(11)
                    ->maxLength(13),
                TextInput::make('nik')
                    ->numeric()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->length(16),
                TextInput::make('role')
                    ->default('STAFF')
                    ->in([
                        'STAFF',
                        'ADMIN'
                    ])
                    ->disabled()
                    ->hidden(fn (Page $livewire) => $livewire instanceof EditUser)
                    ->dehydrated(fn (Page $livewire) => !$livewire instanceof EditUser),
                Select::make('role')
                    ->options([
                        'STAFF' => 'Staff',
                        'ADMIN' => 'Admin',
                    ])
                    ->in([
                        'STAFF',
                        'ADMIN'
                    ])
                    ->afterStateHydrated(function (Select $component, $state) {
                        if (!is_string($state)) return $component->state(head($state));
                        return $component->state($state);
                    })
                    ->hidden(fn (Page $livewire) => $livewire instanceof CreateUser)
                    ->dehydrated(fn (Page $livewire) => !$livewire instanceof CreateUser),
                Textarea::make('address')
                    ->required(),
                Radio::make('status')
                    ->options([
                        'ACTIVE' => 'Active',
                        'INACTIVE' => 'Inactive'
                    ])
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                BadgeColumn::make('role')
                    ->enum([
                        'CUSTOMER' => 'Customer',
                        'MERCHANT' => 'Merchant',
                        'STAFF' => 'Staff',
                        'ADMIN' => 'Admin',
                    ])
                    ->colors([
                        'danger' => 'ADMIN',
                        'secondary' => 'STAFF',
                        'success' => 'MERCHANT',
                        'primary' => 'CUSTOMER'
                    ])
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        '["CUSTOMER"]' => 'Customer',
                        '["MERCHANT"]' => 'Merchant',
                        '["STAFF"]' => 'Staff',
                        '["ADMIN"]' => 'Admin'
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Active',
                        'INACTIVE' => 'Inactive'
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('User deleted successfully'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record:username}'),
            'edit' => Pages\EditUser::route('/{record:username}/edit'),
        ];
    }
}
