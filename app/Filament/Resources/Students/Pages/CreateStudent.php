<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['email']) && ! empty($data['nisn'])) {
            $data['email'] = strtolower(trim((string) $data['nisn'])) . '@siswa.smpn4cepu.sch.id';
        }

        $data['email_verified_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $siswaRole = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        if (! $this->record->hasRole('siswa')) {
            $this->record->assignRole($siswaRole);
        }
    }
}
