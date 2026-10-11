<?php

namespace App\Filament\Resources\StaffMembers;

use App\Filament\Resources\StaffMembers\Pages\CreateStaffMember;
use App\Filament\Resources\StaffMembers\Pages\EditStaffMember;
use App\Filament\Resources\StaffMembers\Pages\ListStaffMembers;
use App\Filament\Resources\StaffMembers\Schemas\StaffMemberForm;
use App\Filament\Resources\StaffMembers\Tables\StaffMembersTable;
use App\Models\StaffMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StaffMemberResource extends Resource
{
    protected static ?string $model = StaffMember::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'nip', 'position'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Jabatan' => $record->position ?? '-',
            'NIP' => $record->nip ?? '-',
        ];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function getNavigationGroup(): ?string
    {
        return 'Profil & Informasi';
    }

    public static function getNavigationLabel(): string
    {
        return 'Guru & Staf';
    }

    public static function getModelLabel(): string
    {
        return 'Guru / Staf';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Data Guru & Staf';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return StaffMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffMembersTable::configure($table);
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
            'index' => ListStaffMembers::route('/'),
            'create' => CreateStaffMember::route('/create'),
            'edit' => EditStaffMember::route('/{record}/edit'),
        ];
    }
}
