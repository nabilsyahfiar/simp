<?php

namespace App\Filament\Foreman\Resources\ProgressReports;

use App\Filament\Foreman\Resources\ProgressReports\Pages\CreateProgressReport;
use App\Filament\Foreman\Resources\ProgressReports\Pages\EditProgressReport;
use App\Filament\Foreman\Resources\ProgressReports\Pages\ListProgressReports;
use App\Filament\Foreman\Resources\ProgressReports\Pages\ViewProgressReport;
use App\Filament\Foreman\Resources\ProgressReports\Schemas\ProgressReportForm;
use App\Filament\Foreman\Resources\ProgressReports\Tables\ProgressReportsTable;
use App\Models\ProgressReport;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ProgressReportResource extends Resource
{
    protected static ?string $model = ProgressReport::class;

    protected static string | UnitEnum | null $navigationGroup = 'Work';

    protected static ?string $navigationLabel = 'Progress Reports';

    protected static ?int $navigationSort = 2;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Schema $schema): Schema
    {
        return ProgressReportForm::configure($schema);
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('foreman', 'progress_reports')
            && RolePermissionAccess::canAccess('foreman', 'progress_reports', 'view');
    }

    public static function table(Table $table): Table
    {
        return ProgressReportsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $userId = auth()->id();

        return parent::getEloquentQuery()
            ->where('foreman_id', $userId)
            ->whereHas('unit', fn (Builder $query) => $query->where('assigned_foreman_id', $userId))
            ->with(['unit.project', 'verifiedBy'])
            ->withCount('photos');
    }

    public static function canCreate(): bool
    {
        return RolePermissionAccess::canAccess('foreman', 'progress_reports', 'create');
    }

    public static function canView(Model $record): bool
    {
        $userId = auth()->id();

        return static::canViewAny()
            && RolePermissionAccess::canAccess('foreman', 'progress_reports', 'view')
            && (int) $record->foreman_id === (int) $userId
            && (int) $record->unit?->assigned_foreman_id === (int) $userId;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canView($record)
            && RolePermissionAccess::canAccess('foreman', 'progress_reports', 'edit')
            && $record->status === 'pending';
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgressReports::route('/'),
            'create' => CreateProgressReport::route('/create'),
            'view' => ViewProgressReport::route('/{record}'),
            'edit' => EditProgressReport::route('/{record}/edit'),
        ];
    }
}
