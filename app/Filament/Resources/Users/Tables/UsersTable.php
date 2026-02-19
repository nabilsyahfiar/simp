<?php

namespace App\Filament\Resources\Users\Tables;

use App\Exports\UsersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('role')
                    ->label('Peran')
                    ->state(fn ($record) => Str::ucfirst((string) $record->getRoleNames()->first()))
                    ->badge(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->state(fn ($record) => $record->is_active ? 'Aktif' : 'Tidak Aktif')
                    ->badge()
                    ->color(fn ($record) => $record->is_active ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(fn () => Role::pluck('name', 'name')
                        ->map(fn (string $name) => Str::ucfirst($name))
                        ->all())
                    ->native(false)
                    ->query(function ($query, array $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->whereHas('roles', function ($roles) use ($data) {
                            $roles->where('name', $data['value']);
                        });
                    }),
                SelectFilter::make('is_active')
                    ->label('Aktif')
                    ->options([
                        1 => 'Aktif',
                        0 => 'Tidak Aktif',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-m-lock-closed' : 'heroicon-m-lock-open')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Pengguna diaktifkan' : 'Pengguna dinonaktifkan')
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
                                ->with('roles')
                                ->get();
                            $timestamp = now()->format('Ymd-His');

                            return Excel::download(new UsersExport($records), "users-{$timestamp}.xlsx");
                        }),
                    Action::make('export_pdf')
                        ->label('PDF (.pdf)')
                        ->icon('heroicon-m-document')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->with('roles')
                                ->get();

                            $headers = [
                                'Nama',
                                'Email',
                                'Username',
                                'Peran',
                                'Status',
                            ];

                            $rows = $records->map(function ($record): array {
                                $role = Str::ucfirst((string) $record->getRoleNames()->first());
                                $status = $record->is_active ? 'Aktif' : 'Tidak Aktif';

                                return [
                                    $record->name,
                                    $record->email,
                                    $record->username,
                                    $role,
                                    $status,
                                ];
                            })->all();

                            $timestamp = now()->format('Ymd-His');

                            $pdf = Pdf::loadView('exports.table', [
                                'title' => 'Pengguna',
                                'headers' => $headers,
                                'rows' => $rows,
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(function () use ($pdf): void {
                                echo $pdf->output();
                            }, "users-{$timestamp}.pdf");
                        }),
                ])
                    ->label('Ekspor')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),
            ]);
    }
}








