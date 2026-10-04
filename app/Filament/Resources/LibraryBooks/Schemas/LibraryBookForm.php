<?php

namespace App\Filament\Resources\LibraryBooks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LibraryBookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('isbn'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('author')
                    ->required(),
                TextInput::make('publisher')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                TextInput::make('publication_year')
                    ->required()
                    ->numeric(),
                TextInput::make('shelf_location')
                    ->required(),
                TextInput::make('total_stock')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('available_stock')
                    ->required()
                    ->numeric()
                    ->default(1),
                FileUpload::make('cover_image')
                    ->image(),
            ]);
    }
}
