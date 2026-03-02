<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\RoleAccessConfig;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(150),
                TextInput::make('username')
                    ->label('Nama Pengguna')
                    ->maxLength(80)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('role')
                    ->label('Peran')
                    ->options(fn () => Role::pluck('name', 'name')
                        ->map(fn (string $name) => RoleAccessConfig::roleLabels()[$name] ?? $name)
                        ->all())
                    ->required()
                    ->searchable()
                    ->afterStateHydrated(function ($component, $record): void {
                        $component->state($record?->getRoleNames()->first());
                    }),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->confirmed()
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn ($record) => $record === null),
                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Kata Sandi')
                    ->password()
                    ->revealable()
                    ->dehydrated(false),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
