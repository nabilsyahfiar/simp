<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\UnitAssignment;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HouseUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'houseUnits';

    protected static ?string $recordTitleAttribute = 'unit_code';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('unit_code')
                    ->required()
                    ->maxLength(50)
                    ->rules([
                        fn ($record) => Rule::unique('house_units', 'unit_code')
                            ->where('project_id', $this->getOwnerRecord()->id)
                            ->ignore($record),
                    ]),
                Select::make('assigned_foreman_id')
                    ->label('Foreman')
                    ->relationship('assignedForeman', 'name', modifyQueryUsing: fn ($query) => $query->role('foreman'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        $previousForemanId = null;

        return $table
            ->columns([
                TextColumn::make('unit_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignedForeman.name')
                    ->label('Foreman')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('official_status')
                    ->label('Status')
                    ->badge()
                    ->state(function ($record): string {
                        $percent = $record->official_progress_percent ?? 0;

                        if ($percent <= 0) {
                            return 'Not started';
                        }

                        if ($percent >= 100) {
                            return 'Completed';
                        }

                        return 'In progress';
                    })
                    ->sortable(),
                TextColumn::make('official_progress_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create House Unit')
                    ->icon('heroicon-m-plus')
                    ->after(function (CreateAction $action): void {
                        $record = $action->getRecord();

                        if (! $record || ! Auth::id()) {
                            return;
                        }

                        UnitAssignment::create([
                            'unit_id' => $record->id,
                            'old_foreman_id' => null,
                            'new_foreman_id' => $record->assigned_foreman_id,
                            'changed_by' => Auth::id(),
                            'changed_at' => now(),
                        ]);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->before(function (EditAction $action) use (&$previousForemanId): void {
                        $previousForemanId = $action->getRecord()?->assigned_foreman_id;
                    })
                    ->after(function (EditAction $action) use (&$previousForemanId): void {
                        $record = $action->getRecord();

                        if (! $record || ! Auth::id()) {
                            return;
                        }

                        if ($previousForemanId === $record->assigned_foreman_id) {
                            return;
                        }

                        UnitAssignment::create([
                            'unit_id' => $record->id,
                            'old_foreman_id' => $previousForemanId,
                            'new_foreman_id' => $record->assigned_foreman_id,
                            'changed_by' => Auth::id(),
                            'changed_at' => now(),
                        ]);
                    }),
                DeleteAction::make(),
            ]);
    }
}
