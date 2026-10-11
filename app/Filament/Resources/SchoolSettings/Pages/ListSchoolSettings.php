<?php

namespace App\Filament\Resources\SchoolSettings\Pages;

use App\Filament\Resources\SchoolSettings\SchoolSettingResource;
use App\Models\SchoolSetting;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSettings extends ListRecords
{
    protected static string $resource = SchoolSettingResource::class;

    protected function getHeaderActions(): array
    {
        // Hanya izinkan tambah jika belum ada data sama sekali
        if (SchoolSetting::count() === 0) {
            return [
                CreateAction::make()->label('Inisialisasi Pengaturan'),
            ];
        }

        return [];
    }
}
