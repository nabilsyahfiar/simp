<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'unit_id',
        'old_status',
        'new_status',
        'old_percent',
        'new_percent',
        'changed_by',
        'changed_at',
        'note',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(HouseUnit::class, 'unit_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
