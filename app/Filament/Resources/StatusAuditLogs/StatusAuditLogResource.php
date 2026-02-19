<?php

namespace App\Filament\Resources\StatusAuditLogs;

use App\Filament\Resources\StatusAuditLogs\Pages\ListStatusAuditLogs;
use App\Filament\Resources\StatusAuditLogs\Pages\ViewStatusAuditLog;
use App\Filament\Resources\StatusAuditLogs\Schemas\StatusAuditLogForm;
use App\Filament\Resources\StatusAuditLogs\Tables\StatusAuditLogsTable;
use App\Models\StatusAuditLog;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StatusAuditLogResource extends Resource
{
    protected static ?string $model = StatusAuditLog::class;

    protected static bool $shouldRegisterNavigation = true;

    protected static string | UnitEnum | null $navigationGroup = 'Audit';

    protected static ?string $navigationLabel = 'Log Audit Status';
    protected static ?string $modelLabel = 'Log Audit Status';
    protected static ?string $pluralModelLabel = 'Log Audit Status';

    protected static ?string $recordTitleAttribute = 'changed_at';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Schema $schema): Schema
    {
        return StatusAuditLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatusAuditLogsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['unit.project', 'changedBy']);
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('admin', 'status_audit_logs')
            && RolePermissionAccess::canAccess('admin', 'status_audit_logs', 'view');
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
            'index' => ListStatusAuditLogs::route('/'),
            'view' => ViewStatusAuditLog::route('/{record}'),
        ];
    }
}
