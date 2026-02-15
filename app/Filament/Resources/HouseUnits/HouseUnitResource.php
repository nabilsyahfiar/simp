<?php

namespace App\Filament\Resources\HouseUnits;

use App\Filament\Resources\HouseUnits\Pages\CreateHouseUnit;
use App\Filament\Resources\HouseUnits\Pages\EditHouseUnit;
use App\Filament\Resources\HouseUnits\Pages\ListHouseUnits;
use App\Filament\Resources\HouseUnits\Schemas\HouseUnitForm;
use App\Filament\Resources\HouseUnits\Tables\HouseUnitsTable;
use App\Models\HouseUnit;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class HouseUnitResource extends Resource
{
    protected static ?string $model = HouseUnit::class;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'House Units';

    protected static ?string $recordTitleAttribute = 'unit_code';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Schema $schema): Schema
    {
        return HouseUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HouseUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('admin', 'house_units')
            && RolePermissionAccess::canAccess('admin', 'house_units', 'view');
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return RolePermissionAccess::canAccess('admin', 'house_units', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return RolePermissionAccess::canAccess('admin', 'house_units', 'edit');
    }

    public static function canDelete(Model $record): bool
    {
        return RolePermissionAccess::canAccess('admin', 'house_units', 'delete');
    }

    public static function canDeleteAny(): bool
    {
        return RolePermissionAccess::canAccess('admin', 'house_units', 'delete');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHouseUnits::route('/'),
            'create' => CreateHouseUnit::route('/create'),
            'edit' => EditHouseUnit::route('/{record}/edit'),
        ];
    }
}
