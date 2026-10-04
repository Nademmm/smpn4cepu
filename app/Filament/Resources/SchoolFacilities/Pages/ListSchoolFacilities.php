<?php

namespace App\Filament\Resources\SchoolFacilities\Pages;

use App\Filament\Resources\SchoolFacilities\SchoolFacilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolFacilities extends ListRecords
{
    protected static string $resource = SchoolFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
