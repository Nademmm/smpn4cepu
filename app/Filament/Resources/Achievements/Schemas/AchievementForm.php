<?php

namespace App\Filament\Resources\Achievements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AchievementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Nama Kejuaraan / Prestasi')
                    ->placeholder('Contoh: Juara 1 Olimpiade Sains Nasional (OSN) Bidang IPA')
                    ->required()
                    ->columnSpanFull(),

                Grid::make(3)->schema([
                    Select::make('category')
                        ->label('Kategori Prestasi')
                        ->options([
                            'Akademik' => 'Akademik',
                            'Olahraga' => 'Olahraga',
                            'Kesenian' => 'Kesenian',
                            'Lingkungan Hidup' => 'Lingkungan Hidup',
                            'Keagamaan' => 'Keagamaan',
                            'Lainnya' => 'Lainnya',
                        ])
                        ->default('Akademik')
                        ->required(),
                    Select::make('level')
                        ->label('Tingkat Kejuaraan')
                        ->options([
                            'Kecamatan' => 'Kecamatan',
                            'Kabupaten' => 'Kabupaten / Kota',
                            'Karesidenan' => 'Karesidenan',
                            'Provinsi' => 'Provinsi',
                            'Nasional' => 'Nasional',
                            'Internasional' => 'Internasional',
                        ])
                        ->default('Kabupaten')
                        ->required(),
                    TextInput::make('rank')
                        ->label('Peringkat / Medali')
                        ->placeholder('Contoh: Juara 1 / Medali Emas')
                        ->required(),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('winner_name')
                        ->label('Nama Peraih / Siswa / Tim')
                        ->placeholder('Contoh: Fathia Rahma Azzahra (Kelas 8A)')
                        ->required(),
                    DatePicker::make('event_date')
                        ->label('Tanggal Perolehan')
                        ->default(now()),
                ]),

                Textarea::make('description')
                    ->label('Keterangan & Catatan Prestasi')
                    ->placeholder('Jelaskan rincian perlombaan atau penyelenggara kegiatan...')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('photo_path')
                    ->label('Foto Piala / Piagam / Dokumentasi Penyerahan')
                    ->image()
                    ->imageEditor()
                    ->directory('achievements')
                    ->disk('public')
                    ->maxSize(5120)
                    ->columnSpanFull(),

                TextInput::make('display_order')
                    ->label('Nomor Urut Tampilan')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
