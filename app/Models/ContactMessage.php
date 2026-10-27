<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email_or_phone',
        'subject',
        'message',
        'ip_address',
        'is_read',
        'read_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('admin_nav_badge_unread_messages');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('admin_nav_badge_unread_messages');
        });
    }
}
