<?php

namespace App\Filament\Resources\Extracurriculars\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ExtracurricularForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nama Ekstrakurikuler')
                        ->placeholder('Contoh: Pramuka Gugus Depan')
                        ->required(),
                    Select::make('category')
                        ->label('Kategori Kegiatan')
                        ->options([
                            'Wajib' => 'Wajib',
                            'Olahraga' => 'Olahraga',
                            'Kesenian' => 'Kesenian',
                            'Kemanusiaan' => 'Kemanusiaan',
                            'Keagamaan' => 'Keagamaan',
                            'Sains & Teknologi' => 'Sains & Teknologi',
                            'Umum' => 'Umum',
                        ])
                        ->default('Olahraga')
                        ->required(),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('leader_name')
                        ->label('Nama Guru Pembina / Pelatih')
                        ->placeholder('Contoh: Bambang Sudarmanto, S.Pd.'),
                    TextInput::make('schedule_day')
                        ->label('Jadwal Latihan Mingguan')
                        ->placeholder('Contoh: Jumat, 14:00 - 16:30 WIB'),
                ]),

                Textarea::make('description')
                    ->label('Deskripsi & Tujuan Ekstrakurikuler')
                    ->placeholder('Jelaskan materi kegiatan, capaian siswa, dan manfaat ekstra ini...')
                    ->rows(3)
                    ->columnSpanFull(),

                FileUpload::make('photo_path')
                    ->label('Foto Dokumentasi Ekstra')
                    ->image()
                    ->imageEditor()
                    ->directory('extracurriculars')
                    ->disk('public')
                    ->maxSize(5120)
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    TextInput::make('display_order')
                        ->label('Nomor Urut Tampilan')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_active')
                        ->label('Status Aktif / Ditampilkan')
                        ->default(true),
                ]),
            ]);
    }
}
