<?php

namespace App\Filament\Management\Widgets;

use App\Models\HouseUnit;
use App\Models\ProgressReport;
use App\Models\Project;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ManagementStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $projects = Project::count();
        $units = HouseUnit::count();
        $averageProgress = (float) (HouseUnit::avg('official_progress_percent') ?? 0);
        $pendingReports = ProgressReport::where('status', 'pending')->count();
        $verifiedReports = ProgressReport::where('status', 'verified')->count();

        return [
            Stat::make('Total Projects', $projects)
                ->description('All projects in the system')
                ->descriptionColor('info')
                ->descriptionIcon('heroicon-m-building-office-2', IconPosition::After),
            Stat::make('Total House Units', $units)
                ->description('All housing units')
                ->descriptionColor('warning')
                ->descriptionIcon('heroicon-m-home-modern', IconPosition::After),
            Stat::make('Overall Progress', number_format($averageProgress, 0) . '%')
                ->description('Average official progress')
                ->descriptionColor('primary')
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::After),
            Stat::make('Pending Reports', $pendingReports)
                ->description('Reports waiting verification')
                ->descriptionColor($pendingReports > 0 ? 'warning' : 'success')
                ->descriptionIcon('heroicon-m-clock', IconPosition::After)
                ->color($pendingReports > 0 ? 'warning' : 'success'),
            Stat::make('Verified Reports', $verifiedReports)
                ->description('Reports verified by staff')
                ->descriptionColor('success')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::After)
                ->color('success'),
        ];
    }
}
