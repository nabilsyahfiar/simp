<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HouseUnit extends Model
{
    protected $attributes = [
        'official_status' => 'belum_mulai',
        'official_progress_percent' => 0,
    ];
    protected $fillable = [
        'project_id',
        'unit_code',
        'official_status',
        'official_progress_percent',
        'assigned_foreman_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedForeman()
    {
        return $this->belongsTo(User::class, 'assigned_foreman_id');
    }

    public function reports()
    {
        return $this->hasMany(ProgressReport::class, 'unit_id');
    }

    public function assignmentHistory()
    {
        return $this->hasMany(UnitAssignment::class, 'unit_id');
    }

    public function statusAuditLogs()
    {
        return $this->hasMany(StatusAuditLog::class, 'unit_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $unit): void {
            $percent = $unit->official_progress_percent ?? 0;

            if ($percent <= 0) {
                $unit->official_status = 'belum_mulai';
            } elseif ($percent >= 100) {
                $unit->official_status = 'selesai';
            } else {
                $unit->official_status = 'dalam_proses';
            }
        });

        static::saved(function (self $unit): void {
            if ($unit->wasRecentlyCreated) {
                return;
            }

            if (! $unit->wasChanged(['official_status', 'official_progress_percent'])) {
                return;
            }

            $userId = Auth::id();

            if (! $userId) {
                return;
            }

            StatusAuditLog::create([
                'unit_id' => $unit->id,
                'old_status' => (string) $unit->getOriginal('official_status'),
                'new_status' => (string) $unit->official_status,
                'old_percent' => $unit->getOriginal('official_progress_percent'),
                'new_percent' => $unit->official_progress_percent,
                'changed_by' => $userId,
                'changed_at' => now(),
            ]);
        });
    }

    public static function generateNextUnitCodeForProject(int $projectId, bool $lockForUpdate = false): string
    {
        $project = Project::query()->findOrFail($projectId);

        $baseCode = strtoupper((string) ($project->code ?: 'PRJ' . $project->id));
        $baseCode = preg_replace('/[^A-Z0-9]/', '', $baseCode) ?: ('PRJ' . $project->id);

        $query = static::query()->where('project_id', $projectId);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $maxNumber = $query
            ->pluck('unit_code')
            ->map(function (string $code) use ($baseCode): int {
                if (! preg_match('/^' . preg_quote($baseCode, '/') . '-(\d+)$/', $code, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return sprintf('%s-%04d', $baseCode, $maxNumber + 1);
    }

    public static function createWithAutoUnitCode(array $attributes, int $maxAttempts = 5): self
    {
        $projectId = (int) ($attributes['project_id'] ?? 0);
        abort_unless($projectId > 0, 422);

        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                /** @var self $created */
                $created = DB::transaction(function () use ($attributes, $projectId): self {
                    $data = $attributes;
                    $data['unit_code'] = static::generateNextUnitCodeForProject($projectId, lockForUpdate: true);

                    return static::query()->create($data);
                }, 3);

                return $created;
            } catch (QueryException $exception) {
                if (
                    $attempt >= $maxAttempts
                    || ! static::isDuplicateUnitCodeException($exception)
                ) {
                    throw $exception;
                }
            }
        }
    }

    private static function isDuplicateUnitCodeException(QueryException $exception): bool
    {
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        if ($driverCode === 1062) {
            $detail = strtolower((string) ($exception->errorInfo[2] ?? ''));

            return str_contains($detail, 'project_id')
                && str_contains($detail, 'unit_code');
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'house_units_project_id_unit_code_unique')
            || (
                str_contains($message, 'unique constraint failed')
                && str_contains($message, 'house_units.project_id')
                && str_contains($message, 'house_units.unit_code')
            )
            || (str_contains($message, 'duplicate') && str_contains($message, 'unit_code'));
    }
}
