<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\CategoryResource;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    public static string $notificationMessage = 'Category successfully ';

    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    return $data;
                })
                ->disableCreateAnother()
                ->successNotificationTitle(self::$notificationMessage . 'created'),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
