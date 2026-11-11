<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CirculationCameraWidget extends Widget
{
    protected static bool $isDiscovered = false;
    protected static ?int $sort = 0;
    protected string $view = 'filament.widgets.circulation-camera-widget';
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'staf_perpus', 'admin']) ?? false;
    }
}
