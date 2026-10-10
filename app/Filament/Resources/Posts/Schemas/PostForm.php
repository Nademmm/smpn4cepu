<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->label('Kategori Konten')
                    ->options(PostCategory::class)
                    ->default(PostCategory::BERITA)
                    ->native(false)
                    ->required(),

                Select::make('author_id')
                    ->label('Penulis / Kontributor')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->required(),

                TextInput::make('title')
                    ->label('Judul Berita / Pengumuman')
                    ->placeholder('Ketik judul berita yang menarik...')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->columnSpanFull()
                    ->required(),

                TextInput::make('slug')
                    ->label('Slug URL (Otomatis)')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('excerpt')
                    ->label('Ringkasan Singkat (Lead Paragraph)')
                    ->placeholder('Ringkasan 1-2 kalimat untuk kartu depan...')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

                RichEditor::make('content')
                    ->label('Isi Berita / Konten Lengkap')
                    ->placeholder('Tulis narasi berita lengkap di sini...')
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->required()
                    ->columnSpanFull(),

                FileUpload::make('featured_image')
                    ->label('Foto Utama / Sampul Berita')
                    ->image()
                    ->imageEditor()
                    ->directory('posts')
                    ->disk('public')
                    ->maxSize(5120) // 5MB
                    ->columnSpanFull(),

                DatePicker::make('event_date')
                    ->label('Tanggal Agenda (Khusus Kategori Agenda)')
                    ->native(false),

                DateTimePicker::make('published_at')
                    ->label('Jadwal Publikasi')
                    ->default(now())
                    ->native(false),

                Toggle::make('is_published')
                    ->label('Publikasikan Sekarang')
                    ->default(true)
                    ->required(),
            ]);
    }
}
