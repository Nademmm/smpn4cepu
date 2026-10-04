<?php

namespace App\Filament\Widgets;

use App\Models\ElectionCandidate;
use Filament\Widgets\ChartWidget;

class PilketosLiveChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'Perolehan Suara Pilketos (Realtime Polling 5s)';
    protected ?string $pollingInterval = '5s';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $candidates = ElectionCandidate::orderBy('candidate_number')->get();

        $labels = $candidates->map(function ($c) {
            return "No. {$c->candidate_number}: {$c->candidate_name}";
        })->toArray();

        $data = $candidates->pluck('total_votes_cached')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Suara Masuk',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                    'borderColor' => [
                        '#2563eb',
                        '#059669',
                        '#d97706',
                        '#dc2626',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
