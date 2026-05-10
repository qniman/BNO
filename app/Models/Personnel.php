<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $table = 'personnel';

    protected $fillable = [
        'codename',
        'real_name',
        'rank',
        'division',
        'profile_type',
        'clearance_level',
        'status',
        'biography',
        'deceased',
    ];

    protected function casts(): array
    {
        return [
            'deceased' => 'boolean',
        ];
    }
}
