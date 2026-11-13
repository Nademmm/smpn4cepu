<?php

namespace App\Filament\Resources\SchoolSettings\Pages;

use App\Filament\Resources\SchoolSettings\SchoolSettingResource;
use App\Models\ActivityLog;
use Filament\Resources\Pages\EditRecord;

class EditSchoolSetting extends EditRecord
{
    protected static string $resource = SchoolSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        ActivityLog::record(
            action: 'update_school_setting',
            description: "Super Admin memperbarui konfigurasi pengaturan sekolah ({$this->record->key}).",
            reason: 'Pembaruan profil/pengaturan operasional sekolah',
            subject: $this->record,
            properties: [
                'key' => $this->record->key,
            ],
            module: 'Pengaturan'
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
