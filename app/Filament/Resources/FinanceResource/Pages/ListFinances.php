<?php

namespace App\Filament\Resources\FinanceResource\Pages;

use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\FinanceResource;

class ListFinances extends ListRecords
{
    protected static string $resource = FinanceResource::class;

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
