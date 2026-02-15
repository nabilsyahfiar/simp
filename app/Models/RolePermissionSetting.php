<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermissionSetting extends Model
{
    protected $fillable = [
        'role',
        'module_key',
        'action',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
