<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $rombelOptions = \App\Support\ClassroomOptions::selectOptions();
        $gradeCohortOptions = $rombelOptions + [
            'Kelas 7' => 'Kelas VII (Umum)',
            'Kelas 8' => 'Kelas VIII (Umum)',
            'Kelas 9' => 'Kelas IX (Umum)',
        ];

        return $schema
            ->components([
                Section::make('Informasi Akun & Hak Akses')
                    ->schema([
                        Grid::make(2)
                            ->schema([
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

                                Select::make('roles')
                                    ->label('Peran / Hak Akses (Role)')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->helperText('Tentukan peran pengguna: siswa, guru, staf_perpus, panitia_pilketos, atau super_admin.'),

                                TextInput::make('password')
                                    ->label('Kata Sandi')
                                    ->password()
                                    ->revealable()
                                    ->minLength(6)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->placeholder(fn (string $operation): string => $operation === 'create' ? 'Tentukan kata sandi akun...' : 'Kosongkan jika tidak ingin mengubah kata sandi'),
                            ]),
                    ]),

                Section::make('Atribut Staf / Peserta Didik (Opsional)')
                    ->description('Isi NIP untuk akun Guru/Staf, atau lengkapi atribut biodata siswa untuk akun Peserta Didik.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('nip')
                                    ->label('NIP (Guru / Staf)')
                                    ->maxLength(25)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Contoh: 198501012010011001'),

                                TextInput::make('nisn')
                                    ->label('NISN (10 Digit)')
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Contoh: 0139549767'),

                                TextInput::make('nis')
                                    ->label('No. Induk / NIS')
                                    ->maxLength(20)
                                    ->placeholder('Contoh: 8968'),
                            ]),

                        Grid::make(5)
                            ->schema([
                                Select::make('student_class')
                                    ->label('Kelas / Rombel (Siswa)')
                                    ->options($gradeCohortOptions)
                                    ->searchable()
                                    ->placeholder('Pilih Rombel...'),

                                Select::make('homeroom_class')
                                    ->label('Kelas Binaan (Wali Kelas)')
                                    ->options($rombelOptions)
                                    ->searchable()
                                    ->placeholder('Khusus Wali Kelas...')
                                    ->helperText('Tentukan rombel binaan untuk membatasi akses Wali Kelas.'),

                                Select::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki (L)',
                                        'P' => 'Perempuan (P)',
                                    ])
                                    ->placeholder('Pilih L/P...'),

                                TextInput::make('birth_place')
                                    ->label('Tempat Lahir')
                                    ->maxLength(100)
                                    ->placeholder('Contoh: BLORA'),

                                DatePicker::make('birth_date')
                                    ->label('Tanggal Lahir')
                                    ->native(false)
                                    ->displayFormat('d M Y'),
                            ]),
                    ]),
            ]);
    }
}
