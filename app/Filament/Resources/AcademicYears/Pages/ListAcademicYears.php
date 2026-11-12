<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcademicYears extends ListRecords
{
    protected static string $resource = AcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Tahun Ajaran (Persiapan)')
                ->icon('heroicon-o-plus')
                ->visible(fn (): bool => auth()->user()?->hasRole('super_admin') ?? false),
        ];
    }
}
