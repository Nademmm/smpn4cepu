<?php

namespace App\Filament\Resources\SchoolFacilities\Pages;

use App\Filament\Resources\SchoolFacilities\SchoolFacilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchoolFacility extends EditRecord
{
    protected static string $resource = SchoolFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
