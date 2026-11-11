<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CirculationCameraWidget extends Widget
{
    protected static bool $isDiscovered = false;
    protected static ?int $sort = 0;
    protected string $view = 'filament.widgets.circulation-camera-widget';
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasAnyRole(['super_admin', 'staf_perpus', 'admin']) ?? false;
    }
}
