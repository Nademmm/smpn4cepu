<?php

namespace App\Filament\Resources\SystemBackups;

use App\Filament\Resources\SystemBackups\Pages\ListSystemBackups;
use App\Filament\Resources\SystemBackups\Tables\SystemBackupsTable;
use App\Models\SystemBackup;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SystemBackupResource extends Resource
{
    protected static ?string $model = SystemBackup::class;

    protected static ?string $recordTitleAttribute = 'filename';

    public static function isSuperAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        return static::isSuperAdmin()
            ? Response::allow()
            : Response::deny('Hanya Super Administrator yang memiliki kewenangan mengelola Backup dan Pemulihan Sistem.');
    }

    public static function canViewAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return static::isSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isSuperAdmin();
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloudArrowUp;

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan & Pengguna';
    }

    public static function getNavigationLabel(): string
    {
        return 'Backup & Pemulihan';
    }

    public static function getModelLabel(): string
    {
        return 'Backup Sistem';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Backup & Pemulihan Sistem';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function table(Table $table): Table
    {
        return SystemBackupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemBackups::route('/'),
        ];
    }
}

