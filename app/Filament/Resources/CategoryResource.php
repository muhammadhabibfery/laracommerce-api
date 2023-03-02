<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\User;
use Filament\Tables;
use App\Models\Category;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\Pages\ManageCategories;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->autofocus()
                    ->required()
                    ->maxLength(55)
                    ->unique(ignoreRecord: true)
                    ->reactive()
                    ->dehydrateStateUsing(fn ($state) => ucwords($state))
                    ->afterStateUpdated(fn (Closure $set, $state) => $set('slug', str($state)->slug()->value()))
                    ->columnSpan('full'),
                Hidden::make('slug')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->form([
                        Card::make()
                            ->schema([
                                TextInput::make('name'),
                                TextInput::make('created_by')
                                    ->formatStateUsing(fn (int $state): string => (User::find($state))->name),

                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle(ManageCategories::$notificationMessage . 'updated'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle(ManageCategories::$notificationMessage . 'deleted'),
            ])
            ->defaultSort('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategories::route('/'),
        ];
    }
}
