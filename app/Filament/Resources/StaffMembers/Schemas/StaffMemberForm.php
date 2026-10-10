<?php

namespace App\Filament\Resources\StaffMembers\Schemas;

use App\Enums\EmploymentStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StaffMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap & Gelar')
                    ->placeholder('Contoh: Dra. Tri Handayani, M.Pd.')
                    ->required(),

                TextInput::make('nip')
                    ->label('NIP / NIPPPK (Opsional)')
                    ->placeholder('19xxxxxxxxxxxxxxxx')
                    ->nullable(),

                TextInput::make('position')
                    ->label('Jabatan / Tugas Mengajar')
                    ->placeholder('Contoh: Kepala Sekolah / Guru Bahasa Indonesia')
                    ->required(),

                Select::make('employment_status')
                    ->label('Status Kepegawaian')
                    ->options(EmploymentStatus::class)
                    ->default(EmploymentStatus::PNS)
                    ->native(false)
                    ->required(),

                FileUpload::make('photo_path')
                    ->label('Foto Profil Pendidik / Tenaga Kependidikan')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->directory('staff')
                    ->disk('public')
                    ->maxSize(3072)
                    ->columnSpanFull(),

                TextInput::make('display_order')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Angka lebih kecil tampil lebih dulu (0, 1, 2, ...)'),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
