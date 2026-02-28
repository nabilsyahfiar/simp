<?php

namespace App\Support;

use App\Models\HouseUnit;
use App\Models\ProgressReport;
use Illuminate\Support\Facades\DB;

class ProgressReportVerifier
{
    public static function verifyByStaff(ProgressReport $report, int $staffId): bool
    {
        return DB::transaction(function () use ($report, $staffId): bool {
            $verifiedAt = now('Asia/Jakarta');

            $updated = ProgressReport::query()
                ->whereKey($report->getKey())
                ->where('status', 'pending')
                ->update([
                    'status' => 'verified',
                    'verified_by' => $staffId,
                    'verified_at' => $verifiedAt,
                ]);

            if ($updated !== 1) {
                return false;
            }

            /** @var ProgressReport|null $freshReport */
            $freshReport = ProgressReport::query()->find($report->getKey());

            if (! $freshReport || ! $freshReport->unit_id) {
                return true;
            }

            /** @var HouseUnit|null $unit */
            $unit = HouseUnit::query()
                ->whereKey($freshReport->unit_id)
                ->lockForUpdate()
                ->first();

            if (! $unit) {
                return true;
            }

            $nextPercent = max(
                (int) ($unit->official_progress_percent ?? 0),
                (int) ($freshReport->reported_percent ?? 0),
            );

            if ((int) ($unit->official_progress_percent ?? 0) !== $nextPercent) {
                $unit->update([
                    'official_progress_percent' => $nextPercent,
                ]);
            }

            return true;
        }, 3);
    }
}

