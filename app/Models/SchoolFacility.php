<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolFacility extends Model
{
    protected $fillable = [
        'name',
        'description',
        'photo_path',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
        ];
    }
}
