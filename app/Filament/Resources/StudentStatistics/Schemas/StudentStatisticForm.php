<?php

namespace App\Filament\Resources\StudentStatistics\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentStatisticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('academic_year')
                    ->label('Tahun Pelajaran')
                    ->required()
                    ->placeholder('Contoh: 2024/2025')
                    ->default(fn () => (date('Y') - 1) . '/' . date('Y')),

                Select::make('grade_level')
                    ->label('Tingkat Kelas')
                    ->options([
                        7 => 'Kelas VII (Tujuh)',
                        8 => 'Kelas VIII (Delapan)',
                        9 => 'Kelas IX (Sembilan)',
                    ])
                    ->required(),

                TextInput::make('class_name')
                    ->label('Nama Rombel / Kelas')
                    ->required()
                    ->placeholder('Contoh: VII-A, VIII-B, IX-C'),

                TextInput::make('male_count')
                    ->label('Jumlah Siswa Laki-laki')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $female = (int) ($get('female_count') ?? 0);
                        $set('total_count', (int) $state + $female);
                    }),

                TextInput::make('female_count')
                    ->label('Jumlah Siswa Perempuan')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $male = (int) ($get('male_count') ?? 0);
                        $set('total_count', (int) $state + $male);
                    }),

                TextInput::make('total_count')
                    ->label('Total Jumlah Siswa (Otomatis)')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Dihitung otomatis dari penjumlahan siswa laki-laki dan perempuan.'),
            ]);
    }
}

