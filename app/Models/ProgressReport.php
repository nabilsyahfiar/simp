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
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'verified_at' => 'datetime',
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
}
