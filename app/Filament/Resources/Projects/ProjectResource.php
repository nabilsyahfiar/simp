<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\RelationManagers\HouseUnitsRelationManager;
use App\Filament\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string | UnitEnum | null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Proyek';
    protected static ?string $modelLabel = 'Proyek';
    protected static ?string $pluralModelLabel = 'Proyek';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

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
        return RoleMenuAccess::canAccess('admin', 'projects')
            && RolePermissionAccess::canAccess('admin', 'projects', 'view');
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return RolePermissionAccess::canAccess('admin', 'projects', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return RolePermissionAccess::canAccess('admin', 'projects', 'edit');
    }

    public static function canDelete(Model $record): bool
    {
        return RolePermissionAccess::canAccess('admin', 'projects', 'delete');
    }

    public static function canDeleteAny(): bool
    {
        return RolePermissionAccess::canAccess('admin', 'projects', 'delete');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
            'view' => ViewProject::route('/{record}'),
        ];
    }
}
