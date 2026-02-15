<?php

namespace App\Filament\Widgets;

use App\Models\HouseUnit;
use App\Models\ProgressReport;
use App\Models\Project;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $projects = Project::count();
        $units = HouseUnit::count();
        $averageProgress = (float) (HouseUnit::avg('official_progress_percent') ?? 0);
        $pendingReports = ProgressReport::where('status', 'pending')->count();

        return [
            Stat::make('Total Projects', $projects)
                ->description('All projects in the system')
                ->descriptionColor('info')
                ->descriptionIcon('heroicon-m-building-office-2', IconPosition::After),
            Stat::make('Total House Units', $units)
                ->description('All housing units')
                ->descriptionColor('warning')
                ->descriptionIcon('heroicon-m-home-modern', IconPosition::After),
            Stat::make('Average Progress', number_format($averageProgress, 0) . '%')
                ->description('Average official progress')
                ->descriptionColor('primary')
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::After),
            Stat::make('Pending Reports', $pendingReports)
                ->description('Reports waiting verification')
                ->descriptionColor($pendingReports > 0 ? 'warning' : 'success')
                ->descriptionIcon('heroicon-m-clipboard-document-list', IconPosition::After)
                ->color($pendingReports > 0 ? 'warning' : 'success'),
        ];
    }
}
