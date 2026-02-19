<?php

namespace App\Filament\Foreman\Resources\AssignedUnits;

use App\Filament\Foreman\Resources\AssignedUnits\Pages\ListAssignedUnits;
use App\Filament\Foreman\Resources\AssignedUnits\Tables\AssignedUnitsTable;
use App\Models\HouseUnit;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AssignedUnitResource extends Resource
{
    protected static ?string $model = HouseUnit::class;

    protected static string | UnitEnum | null $navigationGroup = 'Pekerjaan';

    protected static ?string $navigationLabel = 'Unit Tugas';
    protected static ?string $modelLabel = 'Unit Tugas';
    protected static ?string $pluralModelLabel = 'Unit Tugas';

    protected static ?int $navigationSort = 1;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-home-modern';

    public static function table(Table $table): Table
    {
        return AssignedUnitsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('foreman', 'assigned_units')
            && RolePermissionAccess::canAccess('foreman', 'assigned_units', 'view');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('assigned_foreman_id', auth()->id())
            ->with('project');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignedUnits::route('/'),
        ];
    }
}
