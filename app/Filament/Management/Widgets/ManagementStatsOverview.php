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
            Stat::make('Total Proyek', $projects)
                ->description('Proyek Terdaftar')
                ->descriptionColor('info')
                ->descriptionIcon('heroicon-m-building-office-2', IconPosition::After),
            Stat::make('Total Unit Rumah', $units)
                ->description('Unit Terdaftar')
                ->descriptionColor('warning')
                ->descriptionIcon('heroicon-m-home-modern', IconPosition::After),
            Stat::make('Progres Keseluruhan', number_format($averageProgress, 0) . '%')
                ->description('Progres Rata-rata')
                ->descriptionColor('primary')
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::After),
            Stat::make('Laporan Menunggu Verifikasi', $pendingReports)
                ->description('Perlu Verifikasi')
                ->descriptionColor($pendingReports > 0 ? 'warning' : 'success')
                ->descriptionIcon('heroicon-m-clock', IconPosition::After)
                ->color($pendingReports > 0 ? 'warning' : 'success'),
            Stat::make('Laporan Terverifikasi', $verifiedReports)
                ->description('Sudah Diverifikasi')
                ->descriptionColor('success')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::After)
                ->color('success'),
        ];
    }
}
