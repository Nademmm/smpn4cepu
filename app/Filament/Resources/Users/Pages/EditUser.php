<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\ActivityLog;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (): void {
                    ActivityLog::record(
                        action: 'delete_user_account',
                        description: "Super Admin menghapus akun pengguna {$this->record->name} ({$this->record->email}).",
                        reason: 'Penghapusan akun melalui halaman edit pengguna',
                        subject: $this->record,
                        module: 'Pengaturan'
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        $roles = $this->record->roles()->pluck('name')->implode(', ') ?: '-';

        ActivityLog::record(
            action: 'update_user_account',
            description: "Super Admin memperbarui akun {$this->record->name} (Peran: {$roles}).",
            reason: 'Pembaruan data akun dan hak akses peran pengguna',
            subject: $this->record,
            properties: [
                'email' => $this->record->email,
                'roles' => $roles,
            ],
            module: 'Pengaturan'
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
