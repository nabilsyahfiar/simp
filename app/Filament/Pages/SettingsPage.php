<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\SystemSetting;
use Filament\Notifications\Notification;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string | \UnitEnum | null $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Pengaturan Sistem';
    protected static ?string $title = 'Pengaturan Sistem';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        $inputMode = SystemSetting::where('key', 'progress_input_mode')->value('value') ?? 'percentage';
        
        $this->form->fill([
            'progress_input_mode' => $inputMode,
        ]);
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->columns(['md' => 2])
            ->schema([
                \Filament\Schemas\Components\Section::make('Preferensi Laporan Progres')
                    ->description('Pilih bagaimana mandor akan menginput laporan progres di lapangan.')
                    ->columnSpan(['md' => 1])
                    ->schema([
                        \Filament\Forms\Components\Select::make('progress_input_mode')
                            ->label('Mode Input Progres')
                            ->options([
                                'percentage' => 'Persentase (0-100%)',
                                'checkbox' => 'Checkbox (Selesai/Belum Selesai)',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Jika mengubah mode ini, laporan lama tetap aman, hanya tampilan input yang berubah.'),
                    ])
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SystemSetting::updateOrCreate(
            ['key' => 'progress_input_mode'],
            ['value' => $data['progress_input_mode'], 'description' => 'Mode input laporan progres (percentage atau checkbox)']
        );

        Notification::make()
            ->success()
            ->title('Pengaturan berhasil disimpan')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save')
                ->keyBindings(['mod+s'])
        ];
    }
}
