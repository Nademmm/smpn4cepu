<?php

namespace App\Filament\Resources\QuestionBanks;

use App\Filament\Resources\QuestionBanks\Pages\CreateQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\EditQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\ListQuestionBanks;
use App\Filament\Resources\QuestionBanks\Schemas\QuestionBankForm;
use App\Filament\Resources\QuestionBanks\Tables\QuestionBanksTable;
use App\Models\QuestionBank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuestionBankResource extends Resource
{
    protected static ?string $model = QuestionBank::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'subject.name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Mapel' => $record->subject?->name ?? '-',
            'Jenjang' => 'Kelas ' . ($record->grade_level ?? '-'),
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function getNavigationGroup(): ?string
    {
        return 'Akademik & Pembelajaran';
    }

    public static function getNavigationLabel(): string
    {
        return 'Bank Soal & Ujian';
    }

    public static function getModelLabel(): string
    {
        return 'Bank Soal';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Bank Soal & Ujian';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return QuestionBankForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionBanksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionBanks::route('/'),
            'create' => CreateQuestionBank::route('/create'),
            'edit' => EditQuestionBank::route('/{record}/edit'),
        ];
    }
}
