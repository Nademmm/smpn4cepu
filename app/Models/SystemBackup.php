<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SystemBackup extends Model
{
    use HasFactory;

    protected $table = 'system_backups';

    protected $fillable = [
        'filename',
        'disk',
        'filepath',
        'size_bytes',
        'scope',
        'status',
        'tested_at',
        'test_status',
        'test_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'tested_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (float) $this->size_bytes;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return Carbon::parse($this->created_at)->locale('id')->translatedFormat('d F Y, H.i') . ' WIB';
    }

    public function getFormattedTestedAtAttribute(): string
    {
        if (! $this->tested_at) {
            return 'Belum diuji';
        }

        return Carbon::parse($this->tested_at)->locale('id')->translatedFormat('d F Y, H.i') . ' WIB';
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->filepath);
    }
}
