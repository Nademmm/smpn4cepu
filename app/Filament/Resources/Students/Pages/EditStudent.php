<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected ?string $previousClass = null;

    protected function beforeSave(): void
    {
        $this->previousClass = $this->record->getOriginal('student_class');
    }

    protected function afterSave(): void
    {
        $newClass = $this->record->student_class;
        if ($this->previousClass && $newClass && $this->previousClass !== $newClass) {
            /** @var User|null $actor */
            $actor = Auth::user();
            $actorName = $actor?->name ?? 'Operator';

            ActivityLog::record(
                action: 'move_class',
                description: "{$actorName} memindahkan Siswa {$this->record->name} dari rombel {$this->previousClass} ke {$newClass}.",
                reason: 'penyesuaian pembagian kelas melalui edit profil siswa',
                subject: $this->record,
                properties: [
                    'from_class' => $this->previousClass,
                    'to_class' => $newClass,
                    'student_name' => $this->record->name,
                    'nisn' => $this->record->nisn,
                ],
                module: 'Kesiswaan'
            );
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Data Siswa'),
        ];
    }
}
