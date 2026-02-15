<?php

namespace App\Filament\Staff\Resources\ProgressReports\Pages;

use App\Filament\Staff\Resources\ProgressReports\ProgressReportResource;
use App\Models\ProgressReport;
use App\Support\RolePermissionAccess;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProgressReport extends ViewRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['project_name'] = $record->unit?->project?->name;
        $data['unit_code'] = $record->unit?->unit_code;
        $data['foreman_name'] = $record->foreman?->name;
        $data['status_label'] = $record->status === 'verified' ? 'Verified' : 'Pending';
        $data['verified_by_name'] = $record->verifiedBy?->name;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label(fn (ProgressReport $record): string => $record->status === 'pending' ? 'Verify' : 'Verified')
                ->icon('heroicon-m-check-circle')
                ->color(fn (ProgressReport $record): string => $record->status === 'pending' ? 'success' : 'gray')
                ->requiresConfirmation()
                ->disabled(fn (ProgressReport $record): bool => $record->status !== 'pending'
                    || ! RolePermissionAccess::canAccess('staff', 'progress_reports', 'verify'))
                ->action(function (ProgressReport $record): void {
                    $record->refresh();

                    if ($record->status !== 'pending') {
                        $verifiedBy = $record->verifiedBy?->name ?? 'another staff';
                        $verifiedAt = $record->verified_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-';

                        Notification::make()
                            ->title('Report already verified')
                            ->body("This report was already verified by {$verifiedBy} at {$verifiedAt}.")
                            ->warning()
                            ->send();

                        return;
                    }

                    $record->update([
                        'status' => 'verified',
                        'verified_by' => auth()->id(),
                        'verified_at' => now('Asia/Jakarta'),
                    ]);

                    if ($record->unit) {
                        $record->unit->update([
                            'official_progress_percent' => (int) $record->reported_percent,
                        ]);
                    }

                    Notification::make()
                        ->title('Report verified')
                        ->success()
                        ->send();

                    $this->redirect(ProgressReportResource::getUrl('index'));
                }),
        ];
    }
}
