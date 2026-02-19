<?php

namespace App\Filament\Management\Resources\Projects;

use App\Filament\Management\Resources\Projects\Pages\ListProjects;
use App\Filament\Management\Resources\Projects\Pages\ViewProject;
use App\Filament\Management\Resources\Projects\RelationManagers\HouseUnitsRelationManager;
use App\Filament\Management\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string | UnitEnum | null $navigationGroup = 'Pemantauan';

    protected static ?string $navigationLabel = 'Proyek';
    protected static ?string $modelLabel = 'Proyek';
    protected static ?string $pluralModelLabel = 'Proyek';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HouseUnitsRelationManager::class,
        ];
    }

    public static function canViewAny(): bool
    {
        return RoleMenuAccess::canAccess('management', 'projects')
            && RolePermissionAccess::canAccess('management', 'projects', 'view');
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
            'index' => ListProjects::route('/'),
            'view' => ViewProject::route('/{record}'),
        ];
    }
}
