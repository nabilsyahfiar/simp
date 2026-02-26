<?php

namespace App\Filament\Management\Widgets;

use App\Models\HouseUnit;
use App\Models\ProgressReport;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class ManagementUnitProgressChart extends ChartWidget
{
    use HasFiltersSchema;

    protected string $view = 'filament.widgets.inline-chart-widget';

    protected static ?int $sort = 3;

    protected ?string $heading = 'Progres Unit';

    protected ?string $description = 'Perubahan progres unit berdasarkan laporan terverifikasi.';

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
            Hidden::make('project_id')
                ->default(null),
            Select::make('unit_id')
                ->hiddenLabel()
                ->options(function (): array {
                    $projectId = (int) ($this->filters['project_id'] ?? 0);

                    if ($projectId <= 0) {
                        return [];
                    }

                    return HouseUnit::query()
                        ->with('project')
                        ->when($projectId > 0, fn ($query) => $query->where('project_id', $projectId))
                        ->orderBy('unit_code')
                        ->get()
                        ->mapWithKeys(fn (HouseUnit $unit): array => [
                            $unit->id => "{$unit->unit_code} - {$unit->project?->name}",
                        ])
                        ->all();
                })
                ->native(false)
                ->searchable()
                ->placeholder(fn (): string => ((int) ($this->filters['project_id'] ?? 0) > 0) ? 'Pilih unit' : 'Pilih proyek dulu')
                ->disabled(fn (): bool => (int) ($this->filters['project_id'] ?? 0) <= 0)
                ->default(null),
        ]);
    }

    #[On('management-project-filter-changed')]
    public function handleProjectFilterChanged(int $projectId): void
    {
        $this->filters['project_id'] = $projectId;
        $this->filters['unit_id'] = HouseUnit::query()
            ->where('project_id', $projectId)
            ->orderBy('unit_code')
            ->value('id');

        $this->updateChartData();
    }

    protected function getData(): array
    {
        $unitId = (int) ($this->filters['unit_id'] ?? 0);

        if ($unitId <= 0) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $reports = ProgressReport::query()
            ->where('status', 'verified')
            ->where('unit_id', $unitId)
            ->orderBy('report_date')
            ->get();

        return [
            'labels' => $reports
                ->map(fn (ProgressReport $report): string => $report->report_date->translatedFormat('d M Y'))
                ->all(),
            'datasets' => [
                [
                    'label' => 'Progres Unit (%)',
                    'data' => $reports
                        ->map(fn (ProgressReport $report): float => round((float) $report->reported_percent, 2))
                        ->all(),
                    'fill' => false,
                    'borderColor' => '#0ea5e9',
                    'pointBackgroundColor' => '#0ea5e9',
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
