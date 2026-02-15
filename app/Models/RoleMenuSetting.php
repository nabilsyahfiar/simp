<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenuSetting extends Model
{
    protected $fillable = [
        'role',
        'menu_key',
        'menu_label',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
