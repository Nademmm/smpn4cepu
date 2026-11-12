<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tahun Ajaran')
                    ->placeholder('Contoh: 2027/2028')
                    ->required()
                    ->regex('/^\d{4}\/\d{4}$/')
                    ->unique(ignoreRecord: true)
                    ->helperText('Format: YYYY/YYYY (contoh: 2027/2028)'),

                Select::make('semester')
                    ->label('Semester')
                    ->options([
                        'ganjil' => 'Semester Ganjil',
                        'genap' => 'Semester Genap',
                    ])
                    ->default('ganjil')
                    ->required(),

                Select::make('status')
                    ->label('Status Tahun Ajaran')
                    ->options([
                        'persiapan' => 'Persiapan (Perencanaan Kenaikan Kelas)',
                        'aktif' => 'Aktif (Sedang Berjalan)',
                        'ditutup' => 'Ditutup (Arsip / Riwayat)',
                    ])
                    ->default('persiapan')
                    ->required()
                    ->helperText('Gunakan status "Persiapan" untuk menyusun kenaikan kelas sebelum diaktifkan secara resmi.'),

                DatePicker::make('start_date')
                    ->label('Tanggal Mulai Periode')
                    ->displayFormat('d/m/Y'),

                DatePicker::make('end_date')
                    ->label('Tanggal Selesai Periode')
                    ->displayFormat('d/m/Y'),

                Textarea::make('description')
                    ->label('Keterangan / Catatan Tambahan')
                    ->placeholder('Catatan kurikulum atau penetapan tahun ajaran...')
                    ->columnSpanFull(),
            ]);
    }
}

