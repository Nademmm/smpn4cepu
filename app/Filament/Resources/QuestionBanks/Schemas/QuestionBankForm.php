<?php

namespace App\Filament\Resources\QuestionBanks\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
                    ->options(\App\Enums\GradeLevel::class)
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

                Section::make('Daftar Butir Soal Pilihan Ganda')
                    ->description('Kelola daftar pertanyaan, bobot nilai, pembahasan, dan pilihan jawaban langsung dalam paket soal ini.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('questions')
                            ->label('Butir Soal')
                            ->relationship('questions')
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? 'Butir Soal Baru')
                            ->schema([
                                Textarea::make('question_text')
                                    ->label('Teks Pertanyaan')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('points')
                                    ->label('Bobot Poin')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(10)
                                    ->required(),

                                TextInput::make('order_index')
                                    ->label('Urutan Soal')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),

                                Textarea::make('explanation')
                                    ->label('Pembahasan Jawaban (Opsional)')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Repeater::make('options')
                                    ->label('Pilihan Jawaban')
                                    ->relationship('options')
                                    ->defaultItems(4)
                                    ->minItems(2)
                                    ->columnSpanFull()
                                    ->schema([
                                        TextInput::make('option_label')
                                            ->label('Label Opsi (A/B/C/D)')
                                            ->maxLength(5)
                                            ->placeholder('A'),

                                        TextInput::make('option_text')
                                            ->label('Teks Pilihan Jawaban')
                                            ->required(),

                                        Toggle::make('is_correct')
                                            ->label('Jawaban Benar')
                                            ->default(false),
                                    ])
                                    ->columns(3),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}

