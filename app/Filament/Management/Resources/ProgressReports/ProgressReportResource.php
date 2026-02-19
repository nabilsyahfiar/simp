<?php

namespace App\Filament\Management\Resources\ProgressReports;

use App\Filament\Management\Resources\ProgressReports\Pages\ListProgressReports;
use App\Filament\Management\Resources\ProgressReports\Pages\ViewProgressReport;
use App\Filament\Management\Resources\ProgressReports\Schemas\ProgressReportForm;
use App\Filament\Management\Resources\ProgressReports\Tables\ProgressReportsTable;
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

    protected static string | UnitEnum | null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Progres';
    protected static ?string $modelLabel = 'Laporan Progres';
    protected static ?string $pluralModelLabel = 'Laporan Progres';

    protected static ?string $recordTitleAttribute = 'report_date';

    protected static ?int $navigationSort = 3;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

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
            ->with(['unit.project', 'foreman', 'verifiedBy'])
            ->withCount('photos');
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('management', 'progress_reports')
            && RolePermissionAccess::canAccess('management', 'progress_reports', 'view');
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
