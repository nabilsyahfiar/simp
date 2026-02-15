<?php

namespace App\Filament\Staff\Widgets;

use App\Models\ProgressReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentReportsTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Recent Reports';

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
                    ->label('Report date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('unit.project.name')
                    ->label('Project'),
                TextColumn::make('unit.unit_code')
                    ->label('Unit'),
                TextColumn::make('foreman.name')
                    ->label('Foreman'),
                TextColumn::make('reported_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn (ProgressReport $record): string => $record->status === 'verified' ? 'Verified' : 'Pending')
                    ->color(fn (string $state): string => $state === 'Verified' ? 'success' : 'warning'),
            ])
            ->paginated(false);
    }
}
