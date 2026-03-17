<?php

namespace App\Filament\Resources\Users\Tables;

use App\Exports\UsersExport;
use App\Support\RoleAccessConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->state(function ($record): string {
                        $role = (string) $record->getRoleNames()->first();

                        return RoleAccessConfig::roleLabels()[$role] ?? $role;
                    })
                    ->badge()
                    ->color(fn ($record): string => match ((string) $record->getRoleNames()->first()) {
                        'admin' => 'danger',
                        'management' => 'success',
                        'staff' => 'info',
                        'foreman' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->state(fn ($record) => $record->is_active ? 'Aktif' : 'Tidak Aktif')
                    ->badge()
                    ->color(fn ($record) => $record->is_active ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(fn () => Role::pluck('name', 'name')
                        ->map(fn (string $name) => RoleAccessConfig::roleLabels()[$name] ?? $name)
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
                EditAction::make()->color('warning'),
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
                \App\Filament\Actions\UserModalExport::make(),
            ]);
    }
}







