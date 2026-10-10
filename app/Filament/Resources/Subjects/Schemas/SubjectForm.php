<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Mata Pelajaran')
                    ->required()
                    ->placeholder('Contoh: MTK, IPA, BIND, BING')
                    ->unique(ignoreRecord: true),

                TextInput::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->placeholder('Contoh: Matematika, Ilmu Pengetahuan Alam'),
            ]);
    }
}

