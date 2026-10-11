<?php

namespace App\Filament\Resources\SchoolDocuments\Pages;

use App\Filament\Resources\SchoolDocuments\SchoolDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchoolDocument extends EditRecord
{
    protected static string $resource = SchoolDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
