<?php

namespace App\Filament\Resources\QuizAttempts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuizAttemptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Peserta & Paket Kuis')
                    ->description('Detail pengerjaan kuis evaluasi mandiri oleh peserta didik.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Akun Peserta Didik')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->disabled(),

                                TextInput::make('participant_identifier')
                                    ->label('Identitas Peserta Tercatat')
                                    ->disabled(),

                                Select::make('bank_id')
                                    ->label('Paket Bank Soal')
                                    ->relationship('bank', 'title')
                                    ->disabled()
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Skor & Status Ketuntasan (KKM)')
                    ->description('Guru atau Admin dapat menyesuaikan skor akhir atau status kelulusan jika terdapat koreksi nilai.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('total_questions')
                                    ->label('Total Butir Soal')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('correct_answers')
                                    ->label('Jawaban Benar')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('final_score')
                                    ->label('Nilai Akhir (0 - 100)')
                                    ->numeric()
                                    ->step('0.01')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_passed')
                                    ->label('Tuntas / Lulus KKM')
                                    ->inline(false)
                                    ->required(),

                                DateTimePicker::make('started_at')
                                    ->label('Waktu Mulai')
                                    ->disabled(),

                                DateTimePicker::make('completed_at')
                                    ->label('Waktu Selesai')
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }
}
