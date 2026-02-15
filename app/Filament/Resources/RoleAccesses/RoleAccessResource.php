<?php

namespace App\Filament\Resources\RoleAccesses;

use App\Filament\Resources\RoleAccesses\Pages\EditRoleAccess;
use App\Filament\Resources\RoleAccesses\Pages\ListRoleAccesses;
use App\Filament\Resources\RoleAccesses\Schemas\RoleAccessForm;
use App\Filament\Resources\RoleAccesses\Tables\RoleAccessesTable;
use App\Support\RoleAccessConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleAccessResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string | UnitEnum | null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Role Access';

    protected static ?int $navigationSort = 9;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Schema $schema): Schema
    {
        return RoleAccessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoleAccessesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('name', array_keys(RoleAccessConfig::roles()))
            ->orderBy('name');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
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
            'index' => ListRoleAccesses::route('/'),
            'edit' => EditRoleAccess::route('/{record}/edit'),
        ];
    }
}
