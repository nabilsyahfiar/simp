<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public static function generateNextUnitCodeForProject(int $projectId): string
    {
        $project = Project::query()->findOrFail($projectId);

        $baseCode = strtoupper((string) ($project->code ?: 'PRJ' . $project->id));
        $baseCode = preg_replace('/[^A-Z0-9]/', '', $baseCode) ?: ('PRJ' . $project->id);

        $maxNumber = static::query()
            ->where('project_id', $projectId)
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
}
