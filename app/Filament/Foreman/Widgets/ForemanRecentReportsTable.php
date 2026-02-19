<?php

namespace App\Filament\Foreman\Widgets;

use App\Models\ProgressReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ForemanRecentReportsTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Laporan Terbaru';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProgressReport::query()
                    ->where('foreman_id', auth()->id())
                    ->whereHas('unit', fn ($query) => $query->where('assigned_foreman_id', auth()->id()))
                    ->with(['unit.project', 'verifiedBy'])
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
                TextColumn::make('reported_percent')
                    ->label('Progres')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (ProgressReport $record): string => $record->status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi')
                    ->color(fn (string $state): string => $state === 'Terverifikasi' ? 'success' : 'warning'),
                TextColumn::make('verifiedBy.name')
                    ->label('Diverifikasi oleh')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}

