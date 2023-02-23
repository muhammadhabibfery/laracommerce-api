<?php

namespace App\Filament\Resources\BankingResource\Pages;

use App\Models\User;
use Filament\Resources\Form;
use Filament\Pages\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\BankingResource;

class ViewBanking extends ViewRecord
{
    protected static string $resource = BankingResource::class;

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
                        TextInput::make('name'),
                        TextInput::make('alias'),
                        TextInput::make('created_by')
                            ->formatStateUsing(fn (int $state): string => (User::find($state))->name),
                    ])
            ]);
    }
}
