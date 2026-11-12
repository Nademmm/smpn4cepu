<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'module',
        'action',
        'description',
        'reason',
        'subject_type',
        'subject_id',
        'subject_label',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return Carbon::parse($this->created_at)->locale('id')->translatedFormat('d F Y, H.i');
    }

    /**
     * Catat riwayat aktivitas penting ke database audit log.
     */
    public static function record(
        string $action,
        string $description,
        ?string $reason = null,
        ?Model $subject = null,
        array $properties = [],
        ?string $module = null,
        ?User $actor = null
    ): self {
        /** @var User|null $currentUser */
        $currentUser = $actor ?: Auth::user();

        $actorId = $currentUser?->id;
        $actorName = $currentUser?->name ?? 'Sistem';
        $actorRole = $currentUser?->roles?->pluck('name')->first() ?? ($currentUser ? 'staff' : 'sistem');

        $subjectType = $subject ? get_class($subject) : null;
        $subjectId = $subject?->getKey();
        $subjectLabel = null;

        if ($subject) {
            $subjectLabel = $subject->name
                ?? $subject->title
                ?? $subject->academic_year
                ?? ($subjectId ? '#' . $subjectId : null);
        }

        $ipAddress = null;
        $userAgent = null;

        try {
            if (function_exists('request') && request()) {
                $ipAddress = request()->ip();
                $userAgent = request()->userAgent();
            }
        } catch (\Throwable $e) {
            // Abaikan jika dipanggil di luar konteks HTTP
        }

        return self::create([
            'actor_id' => $actorId,
            'actor_name' => $actorName,
            'actor_role' => $actorRole,
            'module' => $module ?: 'Kesiswaan',
            'action' => $action,
            'description' => $description,
            'reason' => $reason,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
            'properties' => ! empty($properties) ? $properties : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
