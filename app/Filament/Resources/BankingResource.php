<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Banking;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\BankingResource\Pages;

class BankingResource extends Resource
{
    protected static ?string $model = Banking::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?string $recordRouteKeyName = 'alias';

    protected static ?int $navigationSort = 2;


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
                    ->dehydrateStateUsing(fn ($state) => ucwords($state)),
                TextInput::make('alias')
                    ->required()
                    ->maxLength(25)
                    ->unique(ignoreRecord: true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alias')
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Banking deleted successfully'),
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
            'index' => Pages\ListBankings::route('/'),
            'create' => Pages\CreateBanking::route('/create'),
            'view' => Pages\ViewBanking::route('/{record:alias}'),
            'edit' => Pages\EditBanking::route('/{record:alias}/edit'),
        ];
    }
}
