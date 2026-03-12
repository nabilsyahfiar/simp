<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    protected $fillable = [
        'unit_id',
        'foreman_id',
        'description',
        'reported_percent',
        'status',
        'verified_by',
        'verified_at',
        'report_date',
        'category_progress',
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'verified_at' => 'datetime',
        'category_progress' => 'array',
    ];

    public function unit()
    {
        return $this->belongsTo(HouseUnit::class, 'unit_id');
    }

    public function foreman()
    {
        return $this->belongsTo(User::class, 'foreman_id');
    }

    public function photos()
    {
        return $this->hasMany(ReportPhoto::class, 'report_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    protected static function booted()
    {
        static::saving(function ($report) {
            if (is_array($report->category_progress)) {
                $weights = [
                    'pondasi' => 0.10,
                    'bata' => 0.30,
                    'ring_balok' => 0.20,
                    'plafon' => 0.10,
                    'genteng' => 0.05,
                    'acian' => 0.05,
                    'keramik' => 0.05,
                    'pintu' => 0.05,
                    'listrik' => 0.05,
                    'air' => 0.05,
                ];

                $total = 0;
                foreach ($weights as $key => $weight) {
                    $val = floatval($report->category_progress[$key] ?? 0);
                    $total += $val * $weight;
                }
                
                // Cap at 100 just in case
                $report->reported_percent = min(100, round($total, 2));
            }
        });
    }
}
