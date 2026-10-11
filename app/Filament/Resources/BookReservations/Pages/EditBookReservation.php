<?php

namespace App\Filament\Resources\BookReservations\Pages;

use App\Filament\Resources\BookReservations\BookReservationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookReservation extends EditRecord
{
    protected static string $resource = BookReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
