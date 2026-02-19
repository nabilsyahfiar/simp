<?php

namespace App\Filament\Staff\Widgets;

use App\Models\ProgressReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentReportsTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Laporan Terbaru';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProgressReport::query()
                    ->with(['unit.project', 'foreman'])
                    ->latest('report_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('report_date')
                    ->label('Tanggal Laporan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('unit.project.name')
                    ->label('Proyek'),
                TextColumn::make('unit.unit_code')
                    ->label('Unit'),
                TextColumn::make('foreman.name')
                    ->label('Mandor'),
                TextColumn::make('reported_percent')
                    ->label('Progres')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (ProgressReport $record): string => $record->status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi')
                    ->color(fn (string $state): string => $state === 'Terverifikasi' ? 'success' : 'warning'),
            ])
            ->paginated(false);
    }
}
