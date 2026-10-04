<?php

namespace App\Filament\Resources\LibraryBooks\Pages;

use App\Filament\Resources\LibraryBooks\LibraryBookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLibraryBooks extends ListRecords
{
    protected static string $resource = LibraryBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
