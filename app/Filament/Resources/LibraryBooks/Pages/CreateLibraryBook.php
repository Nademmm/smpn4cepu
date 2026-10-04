<?php

namespace App\Filament\Resources\LibraryBooks\Pages;

use App\Filament\Resources\LibraryBooks\LibraryBookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLibraryBook extends CreateRecord
{
    protected static string $resource = LibraryBookResource::class;
}
