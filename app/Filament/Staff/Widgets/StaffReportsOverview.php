<?php

namespace App\Filament\Staff\Widgets;

use App\Models\ProgressReport;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StaffReportsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan Laporan';

    protected function getStats(): array
    {
        $totalReports = ProgressReport::count();
        $unverifiedReports = ProgressReport::where('status', 'pending')->count();
        $verifiedReports = ProgressReport::where('status', 'verified')->count();
        $verificationRate = $totalReports > 0
            ? ($verifiedReports / $totalReports) * 100
            : 0;

        return [
            Stat::make('Total Laporan', $totalReports)
                ->description('Laporan Masuk')
                ->descriptionColor('info')
                ->descriptionIcon('heroicon-m-document-text', IconPosition::After),
            Stat::make('Laporan Belum Verifikasi', $unverifiedReports)
                ->description('Menunggu Verifikasi')
                ->descriptionColor('warning')
                ->descriptionIcon('heroicon-m-clock', IconPosition::After)
                ->color($unverifiedReports > 0 ? 'warning' : 'success'),
            Stat::make('Laporan Terverifikasi', $verifiedReports)
                ->description('Sudah Diverifikasi')
                ->descriptionColor('success')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::After)
                ->color('success'),
            Stat::make('Tingkat Verifikasi', number_format($verificationRate, 1) . '%')
                ->description('Rasio Verifikasi')
                ->descriptionColor($verificationRate >= 80 ? 'success' : ($verificationRate >= 50 ? 'warning' : 'danger'))
                ->descriptionIcon('heroicon-m-chart-bar-square', IconPosition::After)
                ->color($verificationRate >= 80 ? 'success' : ($verificationRate >= 50 ? 'warning' : 'danger')),
        ];
    }
}
