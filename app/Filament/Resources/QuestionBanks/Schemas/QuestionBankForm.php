<?php

namespace App\Filament\Resources\QuestionBanks\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuestionBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('grade_level')
                    ->label('Tingkat Kelas')
                    ->options([
                        7 => 'Kelas VII (Tujuh)',
                        8 => 'Kelas VIII (Delapan)',
                        9 => 'Kelas IX (Sembilan)',
                    ])
                    ->required(),

                TextInput::make('title')
                    ->label('Judul Paket Ujian / Kuis')
                    ->required()
                    ->placeholder('Contoh: Penilaian Harian Bab 1 - Bilangan Bulat')
                    ->columnSpanFull(),

                TextInput::make('duration_minutes')
                    ->label('Durasi Pengerjaan (Menit)')
                    ->required()
                    ->numeric()
                    ->minValue(5)
                    ->maxValue(240)
                    ->default(30)
                    ->suffix('Menit'),

                TextInput::make('passing_grade')
                    ->label('Batas Kelulusan Minimal (KKM)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(75)
                    ->suffix('Poin'),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->helperText('Jika diaktifkan, kuis/soal ini dapat diakses oleh siswa.')
                    ->default(true),

                Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]);
    }
}

