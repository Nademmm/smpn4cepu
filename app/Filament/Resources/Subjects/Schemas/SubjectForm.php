<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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

                Select::make('teachers')
                    ->label('Guru Pengampu Mata Pelajaran')
                    ->relationship(
                        'teachers',
                        'name',
                        fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'guru'))->orWhereNotNull('nip')
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Tentukan guru yang mengampu mapel ini. Saat guru membuat materi atau bank soal, mapel akan otomatis terpilih secara default.'),
            ]);
    }
}
