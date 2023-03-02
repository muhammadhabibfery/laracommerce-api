<?php

namespace App\Filament\Resources\BankingResource\Pages;

use App\Filament\Resources\BankingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankings extends ListRecords
{
    protected static string $resource = BankingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
