<?php

namespace App\Filament\Management\Widgets;

use App\Models\HouseUnit;
use App\Models\ProgressReport;
use App\Models\Project;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ManagementProjectProgressChart extends ChartWidget
{
    use HasFiltersSchema;

    protected string $view = 'filament.widgets.inline-chart-widget';

    protected static ?int $sort = 2;

    protected ?string $heading = 'Progres Proyek';
    protected ?string $description = 'Rata-rata progres proyek berdasarkan persentase terbaru tiap unit pada setiap tanggal laporan.';

    protected ?string $maxHeight = '320px';

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function filters(Schema $schema): Schema
    {
        return $this->filtersSchema($schema);
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('project_id')
                ->hiddenLabel()
                ->options(fn (): array => Project::query()->orderBy('name')->pluck('name', 'id')->all())
                ->native(false)
                ->searchable()
                ->placeholder('Pilih proyek')
                ->selectablePlaceholder()
                ->default(null)
                ->live(),
        ]);
    }

    public function updatedFiltersProjectId($state): void
    {
        $this->dispatch('management-project-filter-changed', projectId: (int) ($state ?: 0));
    }

    protected function getData(): array
    {
        $projectId = (int) ($this->filters['project_id'] ?? 0);

        if ($projectId <= 0) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $unitIds = HouseUnit::query()
            ->where('project_id', $projectId)
            ->pluck('id')
            ->all();

        if ($unitIds === []) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        /** @var Collection<int, ProgressReport> $reports */
        $reports = ProgressReport::query()
            ->where('status', 'verified')
            ->whereIn('unit_id', $unitIds)
            ->orderBy('report_date')
            ->get();

        if ($reports->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $datePoints = $reports
            ->map(fn (ProgressReport $report): string => $report->report_date->toDateString())
            ->unique()
            ->values();

        $timelinesByUnit = $reports
            ->groupBy('unit_id')
            ->map(fn (Collection $unitReports): array => $unitReports
                ->map(fn (ProgressReport $report): array => [
                    'date' => $report->report_date->toDateString(),
                    'percent' => (float) $report->reported_percent,
                ])
                ->values()
                ->all()
            );

        $series = $datePoints->map(function (string $datePoint) use ($unitIds, $timelinesByUnit): float {
            $sum = 0.0;

            foreach ($unitIds as $unitId) {
                $timeline = $timelinesByUnit->get($unitId, []);
                $latestPercent = 0.0;

                foreach ($timeline as $item) {
                    if ($item['date'] > $datePoint) {
                        break;
                    }

                    $latestPercent = (float) $item['percent'];
                }

                $sum += $latestPercent;
            }

            return round($sum / max(count($unitIds), 1), 2);
        });

        return [
            'labels' => $datePoints
                ->map(fn (string $date): string => Carbon::parse($date)->translatedFormat('d M Y'))
                ->all(),
            'datasets' => [
                [
                    'label' => 'Rata-rata Progres (%)',
                    'data' => $series->all(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'borderColor' => '#f59e0b',
                    'pointBackgroundColor' => '#f59e0b',
                    'pointRadius' => 3,
                    'tension' => 0.3,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
