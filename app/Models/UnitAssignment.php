<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitAssignment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'unit_id',
        'old_foreman_id',
        'new_foreman_id',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(HouseUnit::class, 'unit_id');
    }

    public function oldForeman()
    {
        return $this->belongsTo(User::class, 'old_foreman_id');
    }

    public function newForeman()
    {
        return $this->belongsTo(User::class, 'new_foreman_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
