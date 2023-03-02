<?php

namespace App\Filament\Resources\BankingResource\Pages;

use App\Filament\Resources\BankingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanking extends CreateRecord
{
    protected static string $resource = BankingResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Banking created succesfully';
    }
}
