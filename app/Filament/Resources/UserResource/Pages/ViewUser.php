<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\City;
use App\Models\User;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Pages\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Textarea;
use App\Filament\Resources\UserResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('back')
                ->color('secondary')
                ->url($this->getResource()::getUrl('index'))
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('city_id')
                            ->formatStateUsing(fn (int|null $state) => isset($state) ? (City::find($state))->name : '')
                            ->label('City'),
                        TextInput::make('name'),
                        TextInput::make('username'),
                        TextInput::make('email'),
                        TextInput::make('phone'),
                        TextInput::make('role'),
                        Textarea::make('address'),
                        TextInput::make('status'),
                        TextInput::make('created_by')
                            ->formatStateUsing(fn (int|null $state): string => isset($state) ? (User::find($state))->name : ''),
                        TextInput::make('updated_by')
                            ->formatStateUsing(fn (int|null $state): string => isset($state) ? (User::find($state))->name : ''),
                        TextInput::make('deleted_by')
                            ->formatStateUsing(fn (int|null $state): string => isset($state) ? (User::find($state))->name : ''),
                    ])
            ]);
    }
}
