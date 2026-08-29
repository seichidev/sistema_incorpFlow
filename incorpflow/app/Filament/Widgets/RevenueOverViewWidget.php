<?php

namespace App\Filament\Widgets;

use App\Models\Construcao;
use Filament\Widgets\ChartWidget;

class RevenueOverViewWidget extends ChartWidget
{
    protected static ?int $sort = 1;
    protected static ?string $heading = 'Status das Construções';
    protected static ?string $description = 'Distribuição atual';

    protected static ?string $pollingInterval = '30s';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'enabled' => false,
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $statuses = [
            'Em construção',
            'Concluída',
            'Planejamento',
            'Suspensa',
        ];

        $counts = Construcao::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'labels' => $statuses,
            'datasets' => [
                [
                    'data' => collect($statuses)
                        ->map(fn (string $status): int => (int) ($counts[$status] ?? 0))
                        ->all(),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#10b981',
                        '#3b82f6',
                        '#ef4444',
                    ],
                ],
            ],
        ];
    }
}
