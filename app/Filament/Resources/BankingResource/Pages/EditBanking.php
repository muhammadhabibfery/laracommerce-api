<?php

namespace App\Filament\Resources\BankingResource\Pages;

use App\Filament\Resources\BankingResource;
use Filament\Resources\Pages\EditRecord;

class EditBanking extends EditRecord
{
    protected static string $resource = BankingResource::class;

    protected function getActions(): array
    {
        return [
            //
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Banking updated succesfully';
    }
}
