<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $gradeCohortOptions = [
            'Kelas 7' => 'Kelas VII (Angkatan Kelas 7)',
            'Kelas 8' => 'Kelas VIII (Angkatan Kelas 8)',
            'Kelas 9' => 'Kelas IX (Angkatan Kelas 9)',
        ];

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Ahmad Fauzi / Nama Guru'),

                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: ahmad@siswa.smpn4cepu.sch.id'),

                TextInput::make('nisn')
                    ->label('NISN (Nomor Induk Siswa Nasional)')
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: 0081234567')
                    ->helperText('Isi jika akun ini merupakan peserta didik / siswa.'),

                Select::make('student_class')
                    ->label('Tingkat Angkatan Kelas')
                    ->options($gradeCohortOptions)
                    ->placeholder('Pilih tingkat angkatan siswa (Kelas 7 / 8 / 9)...')
                    ->helperText('Pilih tingkat angkatan kelas peserta didik.'),

                TextInput::make('nip')
                    ->label('NIP (Nomor Induk Pegawai)')
                    ->maxLength(25)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: 198501012010011001')
                    ->helperText('Isi jika akun ini merupakan guru atau staf sekolah.'),

                Select::make('roles')
                    ->label('Peran / Hak Akses (Role)')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Tentukan peran pengguna: siswa (akses portal materi & kuis), guru, atau administrator.'),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->minLength(6)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->placeholder(fn (string $operation): string => $operation === 'create' ? 'Tentukan kata sandi akun...' : 'Kosongkan jika tidak ingin mengubah kata sandi'),
            ]);
    }
}
