<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportPhoto extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'file_path',
        'file_size',
        'mime_type',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(ProgressReport::class, 'report_id');
    }
}
