<?php

namespace App\Filament\Staff\Resources\ProgressReports\Pages;

use App\Filament\Staff\Resources\ProgressReports\ProgressReportResource;
use App\Models\ProgressReport;
use App\Support\ProgressReportVerifier;
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
        $data['status_label'] = $record->status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi';
        $data['verified_by_name'] = $record->verifiedBy?->name;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label(fn (ProgressReport $record): string => $record->status === 'pending' ? 'Verifikasi' : 'Terverifikasi')
                ->icon('heroicon-m-check-circle')
                ->color(fn (ProgressReport $record): string => $record->status === 'pending' ? 'success' : 'gray')
                ->requiresConfirmation()
                ->disabled(fn (ProgressReport $record): bool => $record->status !== 'pending'
                    || ! RolePermissionAccess::canAccess('staff', 'progress_reports', 'verify'))
                ->action(function (ProgressReport $record): void {
                    $isVerified = ProgressReportVerifier::verifyByStaff($record, (int) auth()->id());

                    if (! $isVerified) {
                        $record->refresh()->loadMissing('verifiedBy');
                        $verifiedBy = $record->verifiedBy?->name ?? 'staf lain';
                        $verifiedAt = $record->verified_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-';

                        Notification::make()
                            ->title('Laporan sudah diverifikasi')
                            ->body("Laporan ini sudah diverifikasi oleh {$verifiedBy} pada {$verifiedAt}.")
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Laporan berhasil diverifikasi')
                        ->success()
                        ->send();

                    $this->redirect(ProgressReportResource::getUrl('index'));
                }),
        ];
    }
}
