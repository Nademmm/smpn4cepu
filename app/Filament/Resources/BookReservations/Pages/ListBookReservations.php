<?php

namespace App\Filament\Resources\BookReservations\Pages;

use App\Filament\Resources\BookReservations\BookReservationResource;
use Filament\Resources\Pages\ListRecords;

class ListBookReservations extends ListRecords
{
    protected static string $resource = BookReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
