<?php

namespace App\Filament\Resources\ElectionCandidates\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ElectionCandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('candidate_number')
                    ->label('Nomor Urut Paslon')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->placeholder('Contoh: 1, 2, atau 3'),

                TextInput::make('candidate_name')
                    ->label('Nama Calon Ketua OSIS')
                    ->required()
                    ->placeholder('Nama lengkap calon ketua...'),

                TextInput::make('vice_candidate_name')
                    ->label('Nama Calon Wakil Ketua OSIS')
                    ->required()
                    ->placeholder('Nama lengkap calon wakil ketua...'),

                FileUpload::make('photo_path')
                    ->label('Foto Pasangan Calon (Paslon)')
                    ->image()
                    ->directory('candidates')
                    ->disk('public')
                    ->imageEditor()
                    ->helperText('Unggah foto resmi paslon beresolusi jelas (format PNG/JPG).')
                    ->columnSpanFull(),

                Textarea::make('vision')
                    ->label('Visi Paslon')
                    ->required()
                    ->rows(3)
                    ->placeholder('Tuliskan visi kepengurusan paslon di sini...')
                    ->columnSpanFull(),

                Textarea::make('mission')
                    ->label('Misi Paslon')
                    ->required()
                    ->rows(5)
                    ->placeholder("Tuliskan poin-poin misi paslon di sini (gunakan penomoran atau baris baru)...")
                    ->columnSpanFull(),

                TextInput::make('total_votes_cached')
                    ->label('Perolehan Suara (Terkunci Otomatis)')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Jumlah suara terakumulasi otomatis dari proses e-voting pemilih.'),
            ]);
    }
}

