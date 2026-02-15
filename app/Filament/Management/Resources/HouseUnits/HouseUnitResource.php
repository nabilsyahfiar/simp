<?php

namespace App\Filament\Management\Resources\HouseUnits;

use App\Filament\Management\Resources\HouseUnits\Pages\ListHouseUnits;
use App\Filament\Management\Resources\HouseUnits\Tables\HouseUnitsTable;
use App\Models\HouseUnit;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class HouseUnitResource extends Resource
{
    protected static ?string $model = HouseUnit::class;

    protected static string | UnitEnum | null $navigationGroup = 'Monitoring';

    protected static ?string $navigationLabel = 'House Units';

    protected static ?string $recordTitleAttribute = 'unit_code';

    protected static ?int $navigationSort = 2;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-home-modern';

    public static function table(Table $table): Table
    {
        return HouseUnitsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('management', 'house_units')
            && RolePermissionAccess::canAccess('management', 'house_units', 'view');
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
            'index' => ListHouseUnits::route('/'),
        ];
    }
}
