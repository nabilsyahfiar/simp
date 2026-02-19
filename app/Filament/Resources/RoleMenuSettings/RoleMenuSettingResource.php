<?php

namespace App\Filament\Resources\RoleMenuSettings;

use App\Filament\Resources\RoleMenuSettings\Pages\ListRoleMenuSettings;
use App\Filament\Resources\RoleMenuSettings\Tables\RoleMenuSettingsTable;
use App\Models\RoleMenuSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RoleMenuSettingResource extends Resource
{
    protected static ?string $model = RoleMenuSetting::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Pengaturan Menu Peran';
    protected static ?string $modelLabel = 'Pengaturan Menu Peran';
    protected static ?string $pluralModelLabel = 'Pengaturan Menu Peran';

    protected static ?int $navigationSort = 10;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static function table(Table $table): Table
    {
        return RoleMenuSettingsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('role')->orderBy('menu_label');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
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

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoleMenuSettings::route('/'),
        ];
    }
}
