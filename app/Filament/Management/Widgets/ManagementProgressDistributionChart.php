<?php

namespace App\Filament\Management\Widgets;

use App\Models\HouseUnit;
use Filament\Widgets\ChartWidget;

class ManagementProgressDistributionChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Komposisi Progres Unit';

    protected ?string $description = 'Sebaran unit berdasarkan rentang progres saat ini.';

    protected ?string $maxHeight = '320px';

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $bucketCounts = [
            HouseUnit::query()->whereBetween('official_progress_percent', [0, 25])->count(),
            HouseUnit::query()->whereBetween('official_progress_percent', [26, 50])->count(),
            HouseUnit::query()->whereBetween('official_progress_percent', [51, 75])->count(),
            HouseUnit::query()->whereBetween('official_progress_percent', [76, 99])->count(),
            HouseUnit::query()->where('official_progress_percent', '>=', 100)->count(),
        ];

        return [
            'labels' => ['0-25%', '26-50%', '51-75%', '76-99%', '100%'],
            'datasets' => [
                [
                    'label' => 'Jumlah Unit',
                    'data' => $bucketCounts,
                    'backgroundColor' => ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'],
                    'borderColor' => ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
