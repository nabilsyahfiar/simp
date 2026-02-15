<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'code',
        'location',
        'description',
        'status',
        'start_date',
    ];

    public function houseUnits()
    {
        return $this->hasMany(HouseUnit::class);
    }
}
