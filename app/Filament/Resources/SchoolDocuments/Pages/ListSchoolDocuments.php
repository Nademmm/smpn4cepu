<?php

namespace App\Filament\Resources\SchoolDocuments\Pages;

use App\Filament\Resources\SchoolDocuments\SchoolDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolDocuments extends ListRecords
{
    protected static string $resource = SchoolDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Unggah Dokumen Baru'),
        ];
    }
}
