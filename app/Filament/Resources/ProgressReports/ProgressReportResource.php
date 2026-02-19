<?php

namespace App\Filament\Resources\ProgressReports;

use App\Filament\Resources\ProgressReports\Pages\ListProgressReports;
use App\Filament\Resources\ProgressReports\Pages\ViewProgressReport;
use App\Filament\Resources\ProgressReports\Schemas\ProgressReportForm;
use App\Filament\Resources\ProgressReports\Tables\ProgressReportsTable;
use App\Models\ProgressReport;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProgressReportResource extends Resource
{
    protected static ?string $model = ProgressReport::class;

    protected static string | UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Progres';
    protected static ?string $modelLabel = 'Laporan Progres';
    protected static ?string $pluralModelLabel = 'Laporan Progres';

    protected static ?string $recordTitleAttribute = 'report_date';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Schema $schema): Schema
    {
        return ProgressReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgressReportsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['unit.project', 'foreman'])
            ->withCount('photos');
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('admin', 'progress_reports')
            && RolePermissionAccess::canAccess('admin', 'progress_reports', 'view');
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
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
            'view' => ViewProgressReport::route('/{record}'),
        ];
    }
}
