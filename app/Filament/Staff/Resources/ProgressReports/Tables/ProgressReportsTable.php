<?php

namespace App\Filament\Staff\Resources\ProgressReports\Tables;

use App\Exports\ProgressReportsExport;
use App\Models\ProgressReport;
use App\Models\Project;
use App\Support\RolePermissionAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
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
                    ->label('Report date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('unit.project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.unit_code')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('foreman.name')
                    ->label('Foreman')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reported_percent')
                    ->label('Reported progress')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn ($record) => $record->status === 'verified' ? 'Verified' : 'Pending')
                    ->color(fn (string $state) => $state === 'Verified' ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('verified_at')
                    ->label('Verified at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Project')
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
                    ->label('Foreman')
                    ->relationship('foreman', 'name', modifyQueryUsing: fn (Builder $query) => $query->role('foreman'))
                    ->native(false),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->color('info'),
                Action::make('verify')
                    ->label(fn (ProgressReport $record): string => $record->status === 'pending' ? 'Verify' : 'Verified')
                    ->icon('heroicon-m-check-circle')
                    ->color(fn (ProgressReport $record): string => $record->status === 'pending' ? 'success' : 'gray')
                    ->requiresConfirmation()
                    ->disabled(fn (ProgressReport $record): bool => $record->status !== 'pending'
                        || ! RolePermissionAccess::canAccess('staff', 'progress_reports', 'verify'))
                    ->action(function (ProgressReport $record): void {
                        $record->refresh();

                        if ($record->status !== 'pending') {
                            $verifiedBy = $record->verifiedBy?->name ?? 'another staff';
                            $verifiedAt = $record->verified_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-';

                            Notification::make()
                                ->title('Report already verified')
                                ->body("This report was already verified by {$verifiedBy} at {$verifiedAt}.")
                                ->warning()
                                ->send();

                            return;
                        }

                        $record->update([
                            'status' => 'verified',
                            'verified_by' => auth()->id(),
                            'verified_at' => now('Asia/Jakarta'),
                        ]);

                        if ($record->unit) {
                            $record->unit->update([
                                'official_progress_percent' => (int) $record->reported_percent,
                            ]);
                        }

                        Notification::make()
                            ->title('Report verified')
                            ->success()
                            ->send();
                    }),
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
                                'Reported at',
                                'Project',
                                'Unit',
                                'Foreman',
                                'Progress',
                                'Status',
                                'Photos',
                            ];

                            $rows = $records->map(function ($record): array {
                                $status = $record->status === 'verified' ? 'Verified' : 'Pending';

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
                                'title' => 'Progress Reports',
                                'headers' => $headers,
                                'rows' => $rows,
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(function () use ($pdf): void {
                                echo $pdf->output();
                            }, "progress-reports-{$timestamp}.pdf");
                        }),
                ])
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),
            ])
            ->defaultSort('report_date', 'desc');
    }
}

