<?php

namespace App\Filament\Management\Resources\ProgressReports\Tables;

use App\Exports\ProgressReportsExport;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ProgressReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_date')
                    ->label('Tanggal Laporan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('unit.project.name')
                    ->label('Proyek')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('unit.unit_code')
                    ->label('Unit')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('foreman.name')
                    ->label('Mandor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reported_percent')
                    ->label('Progres')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn ($record) => $record->status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi')
                    ->color(fn (string $state) => $state === 'Terverifikasi' ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('verifiedBy.name')
                    ->label('Diverifikasi oleh')
                    ->placeholder('-'),
                TextColumn::make('verified_at')
                    ->label('Tanggal Verifikasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Proyek')
                    ->options(fn () => Project::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $projectId = $data['value'] ?? null;

                        if (! $projectId) {
                            return $query;
                        }

                        return $query->whereHas('unit', fn (Builder $unitQuery) => $unitQuery->where('project_id', $projectId));
                    })
                    ->native(false),
                SelectFilter::make('foreman_id')
                    ->label('Mandor')
                    ->relationship('foreman', 'name')
                    ->native(false),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Verifikasi',
                        'verified' => 'Terverifikasi',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->color('info'),
            ])
            ->toolbarActions([
                ActionGroup::make([
                    Action::make('export_xlsx')
                        ->label('Excel (.xlsx)')
                        ->icon('heroicon-m-table-cells')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->with(['unit.project', 'foreman'])
                                ->withCount('photos')
                                ->get();
                            $timestamp = now()->format('Ymd-His');

                            return Excel::download(new ProgressReportsExport($records), "progress-reports-{$timestamp}.xlsx");
                        }),
                    Action::make('export_pdf')
                        ->label('PDF (.pdf)')
                        ->icon('heroicon-m-document')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->with(['unit.project', 'foreman'])
                                ->withCount('photos')
                                ->get();

                            $headers = [
                                'Tanggal Laporan',
                                'Proyek',
                                'Unit',
                                'Mandor',
                                'Progres',
                                'Status',
                                'Foto',
                            ];

                            $rows = $records->map(function ($record): array {
                                $status = $record->status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi';

                                return [
                                    $record->report_date?->format('Y-m-d H:i'),
                                    $record->unit?->project?->name,
                                    $record->unit?->unit_code,
                                    $record->foreman?->name,
                                    ($record->reported_percent ?? 0) . '%',
                                    $status,
                                    $record->photos_count ?? 0,
                                ];
                            })->all();

                            $timestamp = now()->format('Ymd-His');

                            $pdf = Pdf::loadView('exports.table', [
                                'title' => 'Laporan Progres',
                                'headers' => $headers,
                                'rows' => $rows,
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(function () use ($pdf): void {
                                echo $pdf->output();
                            }, "progress-reports-{$timestamp}.pdf");
                        }),
                ])
                    ->label('Ekspor')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),
            ])
            ->defaultSort('report_date', 'desc');
    }
}

