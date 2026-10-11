<?php

namespace App\Filament\Resources\SchoolSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SchoolSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sambutan Kepala Sekolah')
                    ->description('Pengaturan foto, nama, dan teks sambutan kepala sekolah di halaman beranda')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('principal_name')
                                ->label('Nama Kepala Sekolah & Gelar')
                                ->placeholder('Contoh: Drs. Suharto Widodo, M.Pd.')
                                ->required(),
                            TextInput::make('principal_title')
                                ->label('Jabatan Resmi')
                                ->placeholder('Contoh: Kepala SMP Negeri 4 Cepu')
                                ->required(),
                        ]),
                        FileUpload::make('principal_photo_path')
                            ->label('Foto Resmi Kepala Sekolah')
                            ->image()
                            ->imageEditor()
                            ->directory('principal')
                            ->disk('public')
                            ->maxSize(3072)
                            ->helperText('Format JPG/PNG dengan latar belakang rapi (Maksimal 3MB)'),
                        Textarea::make('principal_welcome_message')
                            ->label('Isi Amanat & Teks Sambutan')
                            ->rows(5)
                            ->placeholder('Tulis pesan sambutan kepala sekolah untuk menyapa siswa dan wali murid...')
                            ->required(),
                    ]),

                Section::make('Identitas & Kontak Resmi')
                    ->description('Alamat fisik, nomor telepon, email resmi, dan tautan Google Maps')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('school_phone')
                                ->label('Nomor Telepon Kantor TU')
                                ->placeholder('(0296) 421631')
                                ->required(),
                            TextInput::make('school_email')
                                ->label('Email Resmi Sekolah')
                                ->email()
                                ->placeholder('smpn4cepu@smpn4cepu.sch.id')
                                ->required(),
                            TextInput::make('whatsapp_contact')
                                ->label('Nomor WhatsApp Layanan Humas')
                                ->placeholder('081234567890'),
                            TextInput::make('postal_code')
                                ->label('Kode Pos')
                                ->placeholder('58315'),
                        ]),
                        Textarea::make('school_address')
                            ->label('Alamat Lengkap Sekolah')
                            ->rows(2)
                            ->required(),
                        Textarea::make('maps_embed_url')
                            ->label('URL Embed Google Maps (iframe src)')
                            ->rows(2)
                            ->helperText('Tautan embed peta lokasi untuk halaman Kontak'),
                    ]),

                Section::make('Media Sosial Resmi Sekolah')
                    ->description('Tautan resmi kanal komunikasi dan informasi sekolah')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('instagram_url')
                                ->label('Instagram URL')
                                ->placeholder('https://instagram.com/smpn4cepu_official'),
                            TextInput::make('youtube_url')
                                ->label('YouTube URL')
                                ->placeholder('https://youtube.com/@smpnegeri4cepu'),
                            TextInput::make('facebook_url')
                                ->label('Facebook URL')
                                ->placeholder('https://facebook.com/smpnegeri4cepu'),
                        ]),
                    ]),

                Section::make('Banner Pengumuman Kilat (Marquee / Alert Beranda)')
                    ->description('Pesan berjalan di bilah atas navbar untuk informasi penting atau genting')
                    ->schema([
                        Toggle::make('announcement_bar_active')
                            ->label('Aktifkan Banner Pengumuman di Atas Navbar')
                            ->default(false),
                        TextInput::make('announcement_bar_text')
                            ->label('Teks Pengumuman')
                            ->placeholder('Contoh: Pendaftaran PPDB Tahun Ajaran Baru Telah Dibuka. Klik di sini untuk info lengkap.')
                            ->columnSpanFull(),
                        TextInput::make('announcement_bar_link')
                            ->label('Tautan Pengumuman (Opsional)')
                            ->placeholder('Contoh: /berita/info-ppdb-2026 atau https://...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
