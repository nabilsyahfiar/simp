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
            Stat::make('Total Proyek', $projects)
                ->description('Proyek Terdaftar')
                ->descriptionColor('info')
                ->descriptionIcon('heroicon-m-building-office-2', IconPosition::After),
            Stat::make('Total Unit Rumah', $units)
                ->description('Unit Terdaftar')
                ->descriptionColor('warning')
                ->descriptionIcon('heroicon-m-home-modern', IconPosition::After),
            Stat::make('Rata-rata Progres', number_format($averageProgress, 0) . '%')
                ->description('Progres Rata-rata')
                ->descriptionColor('primary')
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::After),
            Stat::make('Laporan Menunggu Verifikasi', $pendingReports)
                ->description('Perlu Verifikasi')
                ->descriptionColor($pendingReports > 0 ? 'warning' : 'success')
                ->descriptionIcon('heroicon-m-clipboard-document-list', IconPosition::After)
                ->color($pendingReports > 0 ? 'warning' : 'success'),
        ];
    }
}
