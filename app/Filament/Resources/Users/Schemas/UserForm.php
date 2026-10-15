<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $rombelOptions = [
            '7A' => 'Kelas 7A',
            '7B' => 'Kelas 7B',
            '7C' => 'Kelas 7C',
            '7D' => 'Kelas 7D',
            '7E' => 'Kelas 7E',
            '7F' => 'Kelas 7F',
            '7G' => 'Kelas 7G',
            '8A' => 'Kelas 8A',
            '8B' => 'Kelas 8B',
            '8C' => 'Kelas 8C',
            '8D' => 'Kelas 8D',
            '8E' => 'Kelas 8E',
            '8F' => 'Kelas 8F',
            '8G' => 'Kelas 8G',
            '9A' => 'Kelas 9A',
            '9B' => 'Kelas 9B',
            '9C' => 'Kelas 9C',
            '9D' => 'Kelas 9D',
            '9E' => 'Kelas 9E',
            '9F' => 'Kelas 9F',
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
                    ->label('Kelas / Rombel')
                    ->options($rombelOptions)
                    ->searchable()
                    ->placeholder('Pilih kelas rombel siswa...')
                    ->helperText('Pilih rombel resmi tempat siswa terdaftar.'),

                TextInput::make('nip')
                    ->label('NIP (Nomor Induk Pegawai)')
                    ->maxLength(25)
                    ->placeholder('Contoh: 198501012010011001')
                    ->helperText('Isi jika akun ini merupakan guru atau staf sekolah.'),

                Select::make('roles')
                    ->label('Peran / Hak Akses (Role)')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->helperText('Tentukan peran pengguna: siswa (akses portal materi & kuis), guru, atau administrator.'),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->placeholder(fn (string $operation): string => $operation === 'create' ? 'Tentukan kata sandi akun...' : 'Kosongkan jika tidak ingin mengubah kata sandi'),
            ]);
    }
}
