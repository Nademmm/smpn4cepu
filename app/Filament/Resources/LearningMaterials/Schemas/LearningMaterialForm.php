<?php

namespace App\Filament\Resources\LearningMaterials\Schemas;

use App\Services\LearningMaterialImportService;
use App\Services\Support\FlexibleDocumentReader;
use App\Support\SubjectPermissionFormHelper;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LearningMaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Isi Otomatis dari File Template / Dokumen (Opsional)')
                    ->description('Unggah berkas Word (.docx, .dotx), Excel (.xlsx, .csv), TXT/MD, atau JSON untuk mengisi Judul, Ringkasan, dan Isi Materi secara otomatis.')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('template_extract_file')
                            ->label('Pilih File Template untuk Ekstrak Konten')
                            ->disk('local')
                            ->directory('temp_imports')
                            ->dehydrated(false)
                            ->rules([FlexibleDocumentReader::importFileRule()])
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                $resolved = FlexibleDocumentReader::resolveUploadedFile($state, 'local');
                                if (! $resolved) {
                                    return;
                                }

                                $draft = app(LearningMaterialImportService::class)->extractSingleFormDraft($resolved['path'], $resolved['name']);
                                if (! $draft) {
                                    Notification::make()
                                        ->title('Isi File Belum Terbaca')
                                        ->body('Pastikan berkas Word/Excel/CSV/TXT memiliki teks atau kolom judul yang jelas.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                if ($draft['title'] !== '') {
                                    $set('title', $draft['title']);
                                    $set('slug', $draft['slug']);
                                }
                                if ($draft['summary'] !== '') {
                                    $set('summary', $draft['summary']);
                                }
                                if ($draft['content'] !== '') {
                                    $set('content', $draft['content']);
                                }
                                if (! empty($draft['external_url'])) {
                                    $set('external_url', $draft['external_url']);
                                }

                                Notification::make()
                                    ->title('Template Materi Berhasil Dibaca')
                                    ->body("Judul dan konten diisi otomatis dari {$draft['format_label']}. Silakan tinjau sebelum menyimpan.")
                                    ->success()
                                    ->send();
                            })
                            ->helperText('Mendukung .docx, .dotx, .xlsx, .xltx, .ods, .odt, .csv, .txt, .md, .rtf, dan .json.'),
                    ]),

                SubjectPermissionFormHelper::subjectField(),

                SubjectPermissionFormHelper::gradeLevelField('Tingkat Kelas'),

                SubjectPermissionFormHelper::targetClassesField()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Judul Materi Pembelajaran')
                    ->required()
                    ->placeholder('Contoh: Struktur dan Fungsi Tumbuhan Bagian 1')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label('Slug URL (Otomatis)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Dipergunakan untuk tautan materi pembelajaran di halaman web.')
                    ->columnSpanFull(),

                Textarea::make('summary')
                    ->label('Ringkasan Singkat Materi')
                    ->required()
                    ->rows(3)
                    ->placeholder('Rangkuman kompetensi dasar atau materi yang dipelajari...')
                    ->columnSpanFull(),

                RichEditor::make('content')
                    ->label('Isi Lengkap Materi / Panduan Belajar')
                    ->placeholder('Tuliskan materi ajar lengkap atau penjelasan bagi siswa di sini...')
                    ->columnSpanFull(),

                FileUpload::make('attachment_path')
                    ->label('Berkas Lampiran / Bahan Ajar Digital / Template')
                    ->directory('materials')
                    ->disk('public')
                    ->maxSize(25600)
                    ->rules([FlexibleDocumentReader::materialAttachmentRule()])
                    ->helperText('Mendukung PDF, Word (.doc/.docx/.dotx), Excel (.xls/.xlsx/.xltx/.csv), PowerPoint (.ppt/.pptx/.potx), LibreOffice (.odt/.ods/.odp), TXT/MD/RTF, ZIP/RAR/7Z, atau gambar (maks. 25 MB).')
                    ->columnSpanFull(),

                TextInput::make('external_url')
                    ->label('Tautan Video / Referensi Eksternal (Opsional)')
                    ->url()
                    ->placeholder('https://youtube.com/watch?v=... atau https://drive.google.com/...')
                    ->helperText('Masukkan link jika materi berupa video pembelajaran atau Google Drive publik.')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Publikasikan & Aktifkan Materi Ini')
                    ->default(true),

                Hidden::make('created_by')
                    ->default(fn () => Auth::id()),
            ]);
    }
}

