<?php

namespace App\Filament\Foreman\Widgets;

use App\Models\HouseUnit;
use App\Models\ProgressReport;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ForemanStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan Pekerjaan';

    protected function getStats(): array
    {
        $foremanId = auth()->id();

        $assignedUnits = HouseUnit::query()
            ->where('assigned_foreman_id', $foremanId)
            ->count();

        $inProgressUnits = HouseUnit::query()
            ->where('assigned_foreman_id', $foremanId)
            ->whereBetween('official_progress_percent', [1, 99])
            ->count();

        $completedUnits = HouseUnit::query()
            ->where('assigned_foreman_id', $foremanId)
            ->where('official_progress_percent', '>=', 100)
            ->count();

        $pendingReports = ProgressReport::query()
            ->where('foreman_id', $foremanId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('Unit Tugas', $assignedUnits)
                ->description('Unit Ditugaskan')
                ->descriptionColor('info')
                ->descriptionIcon('heroicon-m-home-modern', IconPosition::After),
            Stat::make('Unit Dalam Proses', $inProgressUnits)
                ->description('Sedang Dikerjakan')
                ->descriptionColor('warning')
                ->descriptionIcon('heroicon-m-wrench-screwdriver', IconPosition::After)
                ->color('warning'),
            Stat::make('Unit Selesai', $completedUnits)
                ->description('Sudah Selesai')
                ->descriptionColor('success')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::After)
                ->color('success'),
            Stat::make('Laporan Menunggu Verifikasi', $pendingReports)
                ->description('Menunggu Verifikasi')
                ->descriptionColor($pendingReports > 0 ? 'warning' : 'success')
                ->descriptionIcon('heroicon-m-clock', IconPosition::After)
                ->color($pendingReports > 0 ? 'warning' : 'success'),
        ];
    }
}
