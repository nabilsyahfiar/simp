<?php

namespace App\Filament\Resources\StatusAuditLogs\Tables;

use App\Exports\StatusAuditLogsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class StatusAuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('changed_at')
                    ->label('Waktu Perubahan')
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
                TextColumn::make('changedBy.name')
                    ->label('Diubah oleh')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('old_status')
                    ->label('Status Lama')
                    ->badge()
                    ->state(fn ($record) => self::mapStatus($record->old_status))
                    ->sortable(),
                TextColumn::make('new_status')
                    ->label('Status Baru')
                    ->badge()
                    ->state(fn ($record) => self::mapStatus($record->new_status))
                    ->sortable(),
                TextColumn::make('old_percent')
                    ->label('Progres Lama')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('new_percent')
                    ->label('Progres Baru')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(40),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'unit_code')
                    ->native(false),
                SelectFilter::make('changed_by')
                    ->label('Diubah oleh')
                    ->relationship('changedBy', 'name')
                    ->native(false),
                SelectFilter::make('new_status')
                    ->label('Status Baru')
                    ->options([
                        'belum_mulai' => 'Not started',
                        'dalam_proses' => 'In progress',
                        'selesai' => 'Selesai',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->color('info'),
            ])
            ->toolbarActions([
                \App\Filament\Actions\StatusAuditLogModalExport::make(),
            ])
            ->defaultSort('changed_at', 'desc');
    }

    private static function mapStatus(?string $status): string
    {
        return match ($status) {
            'belum_mulai' => 'Not started',
            'dalam_proses' => 'In progress',
            'selesai' => 'Selesai',
            default => ucfirst((string) $status),
        };
    }
}








