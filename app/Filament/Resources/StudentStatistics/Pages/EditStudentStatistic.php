<?php

namespace App\Filament\Resources\StudentStatistics\Pages;

use App\Filament\Resources\StudentStatistics\StudentStatisticResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentStatistic extends EditRecord
{
    protected static string $resource = StudentStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
