<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class StudentForm
{
    public static function getRombelOptions(): array
    {
        return [
            'Kelas VII (Angkatan 7)' => [
                '7A' => 'Kelas 7A',
                '7B' => 'Kelas 7B',
                '7C' => 'Kelas 7C',
                '7D' => 'Kelas 7D',
                '7E' => 'Kelas 7E',
                '7F' => 'Kelas 7F',
                '7G' => 'Kelas 7G',
            ],
            'Kelas VIII (Angkatan 8)' => [
                '8A' => 'Kelas 8A',
                '8B' => 'Kelas 8B',
                '8C' => 'Kelas 8C',
                '8D' => 'Kelas 8D',
                '8E' => 'Kelas 8E',
                '8F' => 'Kelas 8F',
                '8G' => 'Kelas 8G',
            ],
            'Kelas IX (Angkatan 9)' => [
                '9A' => 'Kelas 9A',
                '9B' => 'Kelas 9B',
                '9C' => 'Kelas 9C',
                '9D' => 'Kelas 9D',
                '9E' => 'Kelas 9E',
                '9F' => 'Kelas 9F',
            ],
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap Siswa')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: AFIKA ARDINA SARI'),

                TextInput::make('nisn')
                    ->label('NISN (10 Digit)')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: 0137031983')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        if ($state && blank($get('email'))) {
                            $set('email', strtolower(trim($state)) . '@siswa.smpn4cepu.sch.id');
                        }
                    })
                    ->helperText('Digunakan siswa untuk masuk (login) ke portal belajar & kuis.'),

                TextInput::make('nis')
                    ->label('NIS (Nomor Induk Sekolah)')
                    ->maxLength(20)
                    ->placeholder('Contoh: 269772'),

                Select::make('gender')
                    ->label('Jenis Kelamin (L/P)')
                    ->options([
                        'L' => 'Laki-Laki (Putra / L)',
                        'P' => 'Perempuan (Putri / P)',
                    ])
                    ->required()
                    ->native(false),

                Select::make('student_class')
                    ->label('Kelas / Rombongan Belajar')
                    ->options([
                        '7A' => 'Kelas 7A', '7B' => 'Kelas 7B', '7C' => 'Kelas 7C', '7D' => 'Kelas 7D', '7E' => 'Kelas 7E', '7F' => 'Kelas 7F', '7G' => 'Kelas 7G',
                        '8A' => 'Kelas 8A', '8B' => 'Kelas 8B', '8C' => 'Kelas 8C', '8D' => 'Kelas 8D', '8E' => 'Kelas 8E', '8F' => 'Kelas 8F', '8G' => 'Kelas 8G',
                        '9A' => 'Kelas 9A', '9B' => 'Kelas 9B', '9C' => 'Kelas 9C', '9D' => 'Kelas 9D', '9E' => 'Kelas 9E', '9F' => 'Kelas 9F',
                        'Kelas 7' => 'Kelas VII (Umum)',
                        'Kelas 8' => 'Kelas VIII (Umum)',
                        'Kelas 9' => 'Kelas IX (Umum)',
                    ])
                    ->searchable()
                    ->required()
                    ->helperText('Statistik rombel di halaman publik akan otomatis menyesuaikan.'),

                TextInput::make('birth_place')
                    ->label('Tempat Lahir')
                    ->maxLength(100)
                    ->placeholder('Contoh: BLORA'),

                DatePicker::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->displayFormat('d F Y')
                    ->native(false),

                TextInput::make('email')
                    ->label('Email Akun Siswa')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Otomatis: [nisn]@siswa.smpn4cepu.sch.id'),

                TextInput::make('password')
                    ->label('Kata Sandi Akun (Password)')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn (?string $state): string => filled($state) ? Hash::make($state, ['rounds' => 10]) : Hash::make('password123', ['rounds' => 10]))
                    ->dehydrated(fn (?string $state, string $operation): bool => $operation === 'create' || filled($state))
                    ->placeholder('Kosongkan untuk password default: password123')
                    ->helperText('Saat tambah siswa baru, jika dikosongkan otomatis diatur ke: password123'),
            ]);
    }
}
