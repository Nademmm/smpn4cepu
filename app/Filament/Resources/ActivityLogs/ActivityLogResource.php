<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use App\Models\ActivityLog;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $recordTitleAttribute = 'description';

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
            : Response::deny('Hanya Super Administrator yang dapat mengakses Riwayat Log Aktivitas.');
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getNavigationGroup(): ?string
    {
        return 'Pengaturan & Pengguna';
    }

    public static function getNavigationLabel(): string
    {
        return 'Log Aktivitas';
    }

    public static function getModelLabel(): string
    {
        return 'Log Aktivitas';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Riwayat Log Aktivitas';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! static::isSuperAdmin()) {
            return null;
        }

        $count = ActivityLog::whereDate('created_at', today())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['description', 'reason', 'actor_name', 'subject_label'];
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
