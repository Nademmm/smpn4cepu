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

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }

        if (file_exists(public_path('storage/' . $this->photo_path))) {
            return asset('storage/' . $this->photo_path);
        }

        if (file_exists(public_path($this->photo_path))) {
            return asset($this->photo_path);
        }

        if (file_exists(public_path('images/assets/' . $this->photo_path))) {
            return asset('images/assets/' . $this->photo_path);
        }

        return asset('storage/' . $this->photo_path);
    }
}
